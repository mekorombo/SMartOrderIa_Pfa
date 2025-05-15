<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use App\Models\Commande;
use Illuminate\Http\Request;
use App\Models\ProduitCommande;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CommandeController extends Controller
{
    /**
     * Afficher toutes les commandes.
     */
    public function index()
    {
        $commandes = Commande::all();
        return view('commandes.index', compact('commandes'));
    }

    /**
     * Afficher le formulaire de création d'une commande.
     */
    public function create()
    {
        return view('commandes.create');
    }

    /**
     * Enregistrer une nouvelle commande.
     */
    public function store(Request $request)
        {
            DB::beginTransaction();
    try {
        // 🧮 Calcul du total de la commande
        $total = 0;
        foreach ($request->produits as $p) {
            $produit = \App\Models\Produit::find($p['produit_id']);
            if ($produit) {
                $total += $produit->prix * $p['quantite'];
            }
        }

        // 🧾 Création de la commande
        $commande = new Commande();
        $commande->user_id=$request->input('id');
        $commande->total = $total;
        $commande->save();

        // 🧺 Ajout des produits à la commande
        foreach ($request->produits as $p) {
            $produitCommande = new ProduitCommande();
            $produitCommande->commande_id = $commande->id;
            $produitCommande->produit_id = $p['produit_id'];
            $produitCommande->qte = $p['quantite'];
            $produitCommande->save();
        }

        DB::commit();
        return response()->json(['success' => true, 'message' => 'Commande enregistrée avec succès !']);
    } catch (\Exception $e) {
        DB::rollback();
        return response()->json(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()], 500);
    }
        }


    /**
     * Afficher une commande spécifique.
     */
    public function show(Commande $commande)
    {
        return view('commandes.show', compact('commande'));
    }

    /**
     * Afficher le formulaire d'édition d'une commande.
     */
    public function edit(Commande $commande)
    {
        return view('commandes.edit', compact('commande'));
    }

    /**
     * Mettre à jour une commande.
     */
    public function update(Request $request, Commande $commande)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'total' => 'required|numeric',
        ]);

        $commande->update($request->all());

        return redirect()->route('commandes.index')->with('succes', 'Commande mise à jour avec succès.');
    }

    /**
     * Supprimer une commande.
     */
    public function destroy(Commande $commande)
    {
        $commande->delete();

        return redirect()->route('commandes.index')->with('succes', 'Commande supprimée avec succès.');
    }
}
