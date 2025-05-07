<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use Illuminate\Http\Request;

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
        $request->validate([
            'user_id' => 'required|integer',
            'total' => 'required|numeric',
        ]);

        Commande::create($request->all());

        return redirect()->route('commandes.index')->with('succes', 'Commande créée avec succès.');
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
