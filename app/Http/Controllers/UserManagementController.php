<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    /**
     * Afficher tous les utilisateurs.
     */
    public function index()
    {
        $users = User::all();
        return view('usermanagement.index', compact('users'));
    }

    /**
     * Afficher le formulaire de création d'un utilisateur.
     */
    public function create()
    {
        return view('usermanagement.create');
    }

    /**
     * Enregistrer un nouvel utilisateur.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'phone' => 'nullable|numeric',
            'location' => 'nullable|string|max:255',
            'about_me' => 'nullable|string|max:500',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'location' => $request->location,
            'about_me' => $request->about_me,
        ]);

        return redirect()->route('user-management.index')->with('success', 'Utilisateur créé avec succès.');
    }

    /**
     * Afficher un utilisateur spécifique.
     */
    public function show(User $user)
    {
        return view('usermanagement.show', compact('user'));
    }

    /**
     * Afficher le formulaire d'édition d'un utilisateur.
     */
    public function edit(User $user)
    {
        return view('usermanagement.edit', compact('user'));
    }

    /**
     * Mettre à jour un utilisateur existant.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|numeric',
            'location' => 'nullable|string|max:255',
            'about_me' => 'nullable|string|max:500',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'location' => $request->location,
            'about_me' => $request->about_me,
        ]);

        return redirect()->route('user-management.index')->with('success', 'Utilisateur mis à jour avec succès.');
    }

    /**
     * Supprimer un utilisateur.
     */
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('user-management.index')->with('success', 'Utilisateur supprimé avec succès.');
    }
}
