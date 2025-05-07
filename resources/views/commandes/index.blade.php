@extends('layouts.user_type.auth')

@section('content')

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="container">
    <h1>Liste des Commandes</h1>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID Commande</th>
                <th>Nom Utilisateur</th>
                <th>Total</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($commandes as $commande)
            <tr>
                <td>{{ $commande->id }}</td>
                <td>{{ $commande->user->name }}</td> {{-- Attention: il faut la relation dans ton model Commande --}}
                <td>{{ $commande->total }} €</td>
                <td>
                    <a href="{{ route('commandes.show', $commande->id) }}" class="btn btn-info btn-sm">Consulter</a>
                    <form action="{{ route('commandes.destroy', $commande->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Confirmer la suppression ?')">Supprimer</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection
