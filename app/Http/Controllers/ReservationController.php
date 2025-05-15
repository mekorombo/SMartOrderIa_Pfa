<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    
    /**
     * Display the specified resource.
     */
    public function show(Reservation $reservation)
    {
        //
    }
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            // ✅ Validation des données
            $validated = $request->validate([
                'id_restaurant' => 'required|exists:restaurants,id',
                'nom' => 'required|string|max:255',
                'nbre_personnes' => 'required|integer|min:1',
                'heure' => 'required|string',
                'jour' => 'required|date',
            ]);
    
            // 📌 Création de la réservation
            $reservation = new Reservation();
            $reservation->id_restaurant = $validated['id_restaurant'];
            $reservation->nom = $validated['nom'];
            $reservation->nbre_personnes = $validated['nbre_personnes'];
            $reservation->heure = $validated['heure'];
            $reservation->jour = $validated['jour'];
            $reservation->save();
    
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => '✅ Réservation enregistrée avec succès.',
                'data' => $reservation
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '❌ Erreur : ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reservation $reservation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reservation $reservation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reservation $reservation)
    {
        //
    }
}
