from flask import Flask, request, jsonify
import requests
import ollama
from flask_cors import CORS

app = Flask(__name__)
CORS(app)

# Panier et contexte de réservation
panier = []
reservation_context = {
    "step": None,
    "restaurants": [],
    "reservation": {}
}

# Message système pour Ollama
historique_messages = [
    {
        "role": "system",
        "content": (
            "never show ur commande"
            "If the user asks to see the current reservation (e.g. 'show my reservation', 'voir la réservation', 'affiche les infos'), reply ONLY with '#show_reservation'."
            "You are SmartOrderAI, a polite and helpful virtual restaurant assistant. "
            "You speak French and English fluently. "
            "You never invent products and only respond based on the product list provided by the system. \n\n"
            "✅ If the user clearly wants to see the list of available products (e.g. 'je veux voir les produits', 'montre moi le menu'), "
            "reply ONLY with '#get_products'.\n\n"
            "🛒 If the user wants to add a product to the cart (e.g. 'je veux 3 pizzas', 'ajoute 2 couscous'), "
            "reply ONLY with '#add_to_cart:[product_name]:[quantity]'.\n\n"
            "✏️ If the user wants to modify the quantity of an item already in the cart (e.g. 'changer tiramisu à 4', 'modifier tacos à 2'), "
            "reply ONLY with '#update_cart:[product_name]:[new_quantity]'.\n\n"
            "🗑️ If the user wants to remove an item from the cart (e.g. 'supprimer burger', 'enlève le thé'), "
            "reply ONLY with '#remove_from_cart:[product_name]'.\n\n"
            "📦 If the user asks to view the cart (e.g. 'voir mon panier', 'affiche le panier'), "
            "reply ONLY with '#show_cart'.\n\n"
            "📨 If the user wants to confirm the order (e.g. 'je confirme la commande', 'envoyer la commande'), "
            "reply ONLY with '#confirm_order'.\n\n"
            "📅 If the user wants to make a reservation (e.g. 'je veux réserver une table', 'faire une réservation'), "
            "reply ONLY with '#start_reservation'.\n\n"
            "When the system receives '#start_reservation', it will:\n"
            "- Call the API http://127.0.0.1:8000/getRestaurants and show the list of available restaurants.\n"
            "- Wait for the user to select a restaurant (by name or ID).\n"
            "- Then ask for the user's full name.\n"
            "- Then ask for the number of people.\n"
            "- Then ask for the time (e.g. 20h).\n"
            "- Then ask for the day (e.g. 2025-05-13).\n"
            "- Once all details are collected, the system will POST the reservation.\n\n"
            "❗ NEVER create your own menu, restaurants or reservation logic. "
            "NEVER return multiple actions in one message. "
            "Only return the exact action tag as instructed above, nothing else."
        )
    }
]

def get_produits():
    try:
        r = requests.get("http://127.0.0.1:8000/getProduits")
        r.raise_for_status()
        return r.json()
    except:
        return []

def call_ollama_with_memory(msg):
    historique_messages.append({"role": "user", "content": msg})
    res = ollama.chat(model="llama3", messages=historique_messages)
    message = res["message"]["content"]
    historique_messages.append({"role": "assistant", "content": message})
    return message
@app.route("/reset", methods=["POST"])
def reset_chat_memory():
    global historique_messages, panier, reservation_context
    historique_messages = historique_messages[:1]  # garde uniquement le message system
    panier = []
    reservation_context = {
        "step": None,
        "restaurants": [],
        "reservation": {}
    }
    return jsonify({"status": "ok", "message": "Mémoire du chatbot réinitialisée."})
@app.route("/chat", methods=["POST"])
def chat():
    user_input = request.json.get("message")
    user_id = request.json.get("user_id")
    produits = get_produits()
    global panier, reservation_context

    response = call_ollama_with_memory(user_input)
    r_lower = response.lower().strip()

    if "#get_products" in r_lower:
        if not produits:
            return jsonify({"response": "❌ Aucun produit trouvé."})
        texte = "\n".join([f"{p['nom']} - {p['prix']} MAD\n{p['description']}" for p in produits])
        texte += "\n\n👉 Souhaitez-vous passer une commande ? Si oui, indiquez le produit et la quantité. ex('Je veux 15 Tiramissu')"
        return jsonify({"response": f"📋 Produits disponibles :\n\n{texte}"})

    elif "#add_to_cart:" in r_lower:
        try:
            parts = response.split(":")
            if len(parts) < 3:
                raise ValueError("Format invalide")
            nom, quantite = parts[1].strip(), int(parts[2].strip())
            produit = next((p for p in produits if p['nom'].lower() == nom.lower()), None)
            if not produit:
                return jsonify({"response": f"❌ Produit '{nom}' introuvable."})
            existant = next((i for i in panier if i['produit_id'] == produit['id']), None)
            if existant:
                existant["quantite"] += quantite
                return jsonify({"response": f"🔄 Quantité mise à jour : {existant['quantite']} x {produit['nom']}"} )
            else:
                panier.append({
                    "produit_id": produit["id"],
                    "nom": produit["nom"],
                    "quantite": quantite,
                    "prix": float(produit["prix"])
                })
                return jsonify({"response": f"✅ Ajouté : {quantite} x {produit['nom']} au panier.\n\n📝 Si vous souhaitez confirmer votre commande, tapez : confirmer ma commande."})
        except Exception as e:
            return jsonify({"response": f"❌ Erreur : {str(e)}"})
    elif "show_reservation" in response.lower():
        r = reservation_context["reservation"]
        if not r:
            return jsonify({"response": "ℹ️ Aucune donnée de réservation en cours."})
        texte = "📋 Détails de la réservation en cours :\n"
        texte += f"Nom : {r.get('nom', 'Non défini')}\n"
        texte += f"Restaurant ID : {r.get('id_restaurant', 'Non défini')}\n"
        texte += f"Personnes : {r.get('nbre_personnes', 'Non défini')}\n"
        texte += f"Heure : {r.get('heure', 'Non défini')}\n"
        texte += f"Jour : {r.get('jour', 'Non défini')}"
        return jsonify({"response": texte})

    elif "#update_cart:" in r_lower:
        try:
            parts = response.split(":")
            if len(parts) < 3:
                raise ValueError("Format invalide")
            nom, qte = parts[1].strip(), int(parts[2].strip())
            for item in panier:
                if item["nom"].lower() == nom.lower():
                    item["quantite"] = qte
                    return jsonify({"response": f"🔄 Quantité mise à jour : {qte} x {item['nom']}"} )
            return jsonify({"response": "❌ Produit non trouvé dans le panier."})
        except:
            return jsonify({"response": "❌ Erreur lors de la mise à jour du panier. \n\n📝 Si vous souhaitez confirmer votre commande, tapez : confirmer ma commande."})

    elif "#remove_from_cart:" in r_lower:
        try:
            nom = response.split(":")[1].strip()
            panier[:] = [i for i in panier if i["nom"].lower() != nom.lower()]
            return jsonify({"response": f"🗑️ {nom} retiré du panier."})
        except:
            return jsonify({"response": "❌ Format invalide pour la suppression. \n\n📝 Si vous souhaitez confirmer votre commande, tapez : confirmer ma commande."})

    elif "#show_cart" in r_lower:
        if not panier:
            return jsonify({"response": "🛒 Votre panier est vide."})
        lignes, total = [], 0
        for item in panier:
            sous_total = item["quantite"] * item["prix"]
            lignes.append(f"- {item['quantite']} x {item['nom']} ({item['prix']} MAD) = {sous_total} MAD")
            total += sous_total
        texte = "\n".join(lignes) + f"\n\n💰 Total général : {total} MAD"
        return jsonify({"response": f"🛒 Contenu du panier :\n{texte}"})

    elif "#confirm_order" in r_lower:
        if len(panier) < 1:
            return jsonify({"response": "🛒 Votre panier est vide. Ajoutez des produits avant de confirmer votre commande."})

        donnees = {
            "produits": [{"produit_id": i["produit_id"], "quantite": i["quantite"]} for i in panier],
            "id": user_id
        }

        try:
            r = requests.post("http://127.0.0.1:8000/api/Commandes", json=donnees)
            r.raise_for_status()
            panier.clear()
            return jsonify({"response": "✅ Commande envoyée avec succès !"})
        except Exception as e:
            return jsonify({"response": f"❌ Erreur lors de l’envoi de la commande : {e}"})

    elif "#start_reservation" in r_lower:
        try:
            res = requests.get("http://127.0.0.1:8000/getRestaurants")
            res.raise_for_status()
            restaurants = res.json()
            if not restaurants:
                return jsonify({"response": "❌ Aucun restaurant disponible pour le moment."})
            reservation_context.update({
                "step": "choose_restaurant",
                "restaurants": restaurants,
                "reservation": {}
            })
            texte = "🍽️ Restaurants disponibles :\n" + "\n".join([f"{r['id']}. {r['nom']}" for r in restaurants])
            texte += "\n\n👉 Entrez le **numéro** du restaurant choisi."
            return jsonify({"response": texte})
        except Exception as e:
            return jsonify({"response": f"❌ Erreur récupération restaurants : {str(e)}"})

    # Étapes de réservation
    elif reservation_context["step"] == "choose_restaurant":
        try:
            restaurant_id = int(user_input.strip())
            if not any(r["id"] == restaurant_id for r in reservation_context["restaurants"]):
                return jsonify({"response": "❌ Restaurant non trouvé. ID invalide."})
            reservation_context["reservation"]["id_restaurant"] = restaurant_id
            reservation_context["step"] = "ask_name"
            return jsonify({"response": "📝 Entrez votre nom complet."})
        except:
            return jsonify({"response": "❌ Veuillez entrer un ID valide."})

    elif reservation_context["step"] == "ask_name":
        reservation_context["reservation"]["nom"] = user_input.strip()
        reservation_context["step"] = "ask_people"
        return jsonify({"response": "👥 Combien de personnes ?"})

    elif reservation_context["step"] == "ask_people":
        try:
            n = int(user_input.strip())
            if n <= 0:
                return jsonify({"response": "❌ Nombre de personnes invalide."})
            reservation_context["reservation"]["nbre_personnes"] = n
            reservation_context["step"] = "ask_time"
            return jsonify({"response": "🕒 À quelle heure ? (ex : 20h)"})
        except:
            return jsonify({"response": "❌ Entrez un nombre valide."})

    elif reservation_context["step"] == "ask_time":
        reservation_context["reservation"]["heure"] = user_input.strip()
        reservation_context["step"] = "ask_day"
        return jsonify({"response": "📅 Pour quel jour ? (ex : 2025-05-13)"})

    elif reservation_context["step"] == "ask_day":
        reservation_context["reservation"]["jour"] = user_input.strip()
        return confirm_reservation()

    return jsonify({"response": response})

def confirm_reservation():
    global reservation_context
    try:
        data = reservation_context["reservation"]
        r = requests.post("http://127.0.0.1:8000/api/Reservation", json=data)
        r.raise_for_status()
        msg = (
            "✅ Réservation enregistrée !\n"
            f"Nom : {data['nom']}\n"
            f"Restaurant ID : {data['id_restaurant']}\n"
            f"Personnes : {data['nbre_personnes']}\n"
            f"Heure : {data['heure']}\n"
            f"Jour : {data['jour']}"
        )
        reservation_context = {"step": None, "restaurants": [], "reservation": {}}
        return jsonify({"response": msg})
    except Exception as e:
        return jsonify({"response": f"❌ Erreur enregistrement réservation : {str(e)}"})

if __name__ == "__main__":
    app.run(debug=True)
