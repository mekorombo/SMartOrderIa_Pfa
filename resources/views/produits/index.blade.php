@extends('layouts.user_type.auth')

@section('content')
<div class="container">
    <h1>Liste des Produits</h1>
    @if (session('succes'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('succes') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <a href="{{ route('produits.create') }}" class="btn btn-primary mb-3">Ajouter un Produit</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Prix</th>
                <th>Quantité</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($produits as $produit)
            <tr>
                <td>{{ $produit->id }}</td>
                <td>{{ $produit->nom }}</td>
                <td>{{ $produit->prix }}</td>
                <td>{{ $produit->qte }}</td>
                <td>{{ $produit->description }}</td>
                <td>
                    <a href="{{ route('produits.show', $produit->id) }}" class="btn btn-warning btn-sm">Éditer</a>
                    <form action="{{ route('produits.destroy', $produit->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr ?')">Supprimer</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
