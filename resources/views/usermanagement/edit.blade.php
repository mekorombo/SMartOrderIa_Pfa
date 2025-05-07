@extends('layouts.user_type.auth')

@section('content')

<div class="container">
    <h1>Éditer l'Utilisateur</h1>

    <form action="{{ route('user-management.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label>Nom :</label>
            <input type="text" name="name" value="{{ $user->name }}" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Email :</label>
            <input type="email" name="email" value="{{ $user->email }}" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Téléphone :</label>
            <input type="text" name="phone" value="{{ $user->phone }}" class="form-control">
        </div>

        <div class="form-group">
            <label>Localisation :</label>
            <input type="text" name="location" value="{{ $user->location }}" class="form-control">
        </div>

        <div class="form-group">
            <label>À propos :</label>
            <textarea name="about_me" class="form-control">{{ $user->about_me }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary mt-3">Mettre à jour</button>
    </form>
</div>

@endsection
