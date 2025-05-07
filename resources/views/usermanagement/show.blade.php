@extends('layouts.user_type.auth')

@section('content')

<div class="container">
    <h1>Détails de l'Utilisateur</h1>

    <ul class="list-group">
        <li class="list-group-item"><strong>ID :</strong> {{ $user->id }}</li>
        <li class="list-group-item"><strong>Nom :</strong> {{ $user->name }}</li>
        <li class="list-group-item"><strong>Email :</strong> {{ $user->email }}</li>
        <li class="list-group-item"><strong>Téléphone :</strong> {{ $user->phone }}</li>
        <li class="list-group-item"><strong>Localisation :</strong> {{ $user->location }}</li>
        <li class="list-group-item"><strong>À propos :</strong> {{ $user->about_me }}</li>
    </ul>

    <a href="{{ route('user-management.index') }}" class="btn btn-secondary mt-3">Retour à la liste</a>
</div>

@endsection
