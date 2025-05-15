from flask import Flask, request, jsonify
import requests
import ollama
import re
from flask_cors import CORS

app = Flask(__name__)
CORS(app)  # Autorise les requêtes Cross-Origin (HTML → Flask)

panier = []
historique_messages = [
    {
        "role": "system",
        "content": (
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
            "The cart should be managed internally by the system. Each product stored in the cart must include: "
            "the product ID (from the API), the name, and the quantity. You display only the name and quantity to the user. "
            "When confirming the order, send only the product_id and quantity.\n\n"
            "❗ NEVER create your own menu or products, and NEVER return multiple actions in one message. "
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

@app.route("/chat", methods=["POST"])
def chat():
    user_input = request.json.get("message")
    user_id = request.json.get("user_id") 
    produits = get_produits()
    global panier

    response = call_ollama_with_memory(user_input)


    if response == "#get_products":
        if not produits:
            return jsonify({"response": "❌ Aucun produit trouvé."})
        
        texte = "\n".join([
            f"{p['nom']} - {p['prix']} MAD\n{p['description']}"
            for p in produits
        ])
        texte += "\n\n👉 Souhaitez-vous passer une commande ? Si oui, indiquez le produit et la quantité."

        return jsonify({"response": f"📋 Produits disponibles :\n\n{texte}"})
    elif response.startswith("#add_to_cart:"):
        try:
            parts = response.split(":")
            if len(parts) != 3:
                return jsonify({"response": "❌ Format invalide. Exemple attendu : #add_to_cart:Tiramisu:2"})

            _, nom, quantite = parts
            produit = next((p for p in produits if p['nom'].lower() == nom.lower()), None)
            if not produit:
                return jsonify({"response": f"❌ Produit '{nom}' introuvable dans le menu."})

            quantite = int(quantite)
            existant = next((i for i in panier if i['produit_id'] == produit['id']), None)
            if existant:
                existant["quantite"] += quantite
                return jsonify({"response": f"🔄 Quantité mise à jour : {existant['quantite']} x {produit['nom']}"})
            else:
                panier.append({
                    "produit_id": produit["id"],
                    "nom": produit["nom"],
                    "quantite": quantite,
                    "prix": float(produit["prix"])
                })
                return jsonify({"response": f"✅ Ajouté : {quantite} x {produit['nom']} au panier."})

        except Exception as e:
            return jsonify({"response": f"❌ Erreur technique : {str(e)}"})


    elif response.startswith("#update_cart:"):
        try:
            _, nom, qte = response.split(":")
            for item in panier:
                if item["nom"].lower() == nom.lower():
                    item["quantite"] = int(qte)
                    return jsonify({"response": f"🔄 Quantité mise à jour : {qte} x {item['nom']}"})
            return jsonify({"response": "❌ Produit non trouvé dans le panier."})
        except:
            return jsonify({"response": "❌ Erreur lors de la mise à jour du panier."})

    elif response.startswith("#remove_from_cart:"):
        _, nom = response.split(":")
        panier[:] = [i for i in panier if i["nom"].lower() != nom.lower()]
        return jsonify({"response": f"🗑️ {nom} retiré du panier."})

    elif response == "#show_cart":
        if not panier:
            return jsonify({"response": "🛒 Votre panier est vide."})

        texte_lignes = []
        total = 0
        for item in panier:
            ligne_total = item["quantite"] * item["prix"]
            texte_lignes.append(f"- {item['quantite']} x {item['nom']} ({item['prix']} MAD) = {ligne_total} MAD")
            total += ligne_total

        texte_panier = "\n".join(texte_lignes)
        texte_panier += f"\n\n💰 Total général : {total} MAD"

        return jsonify({"response": f"🛒 Contenu du panier :\n{texte_panier}"})

    elif response == "#confirm_order":
        donnees = {"produits": [{"produit_id": i["produit_id"], "quantite": i["quantite"]} for i in panier] , "id" : user_id}
        print("📦 Données envoyées à Laravel :")
        print(donnees)
        try:
            r = requests.post("http://127.0.0.1:8000/api/Commandes", json=donnees)
            r.raise_for_status()
            panier.clear()
            return jsonify({"response": "✅ Commande envoyée avec succès !"})
        except Exception as e:
            return jsonify({"response": f"❌ Erreur lors de l’envoi de la commande : {e}"})

    else:
        return jsonify({"response": response})

if __name__ == "__main__":
    app.run(debug=True)