@extends('layouts.user_type.auth')

@section('content')
<div class="container">
    <h2 class="mb-4">Liste des Restaurants</h2>

    <a href="{{ route('restaurants.create') }}" class="btn btn-primary mb-3">Ajouter un restaurant</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($restaurants as $restaurant)
                <tr>
                    <td>{{ $restaurant->id }}</td>
                    <td>{{ $restaurant->nom }}</td>
                    <td>
                        <a href="{{ route('restaurants.edit', $restaurant->id) }}" class="btn btn-sm btn-warning">Modifier</a>
                        <form action="{{ route('restaurants.destroy', $restaurant->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce restaurant ?')">Supprimer</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
