@extends('layouts.user_type.auth')

@section('content')

<div class="container">
    <h1>Ajouter un Utilisateur</h1>

    <form action="{{ route('user-management.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label>Nom :</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Email :</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Mot de passe :</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Confirmer mot de passe :</label>
            <input type="password" name="password_confirmation" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Téléphone :</label>
            <input type="text" name="phone" class="form-control">
        </div>

        <div class="form-group">
            <label>Localisation :</label>
            <input type="text" name="location" class="form-control">
        </div>

        <div class="form-group">
            <label>À propos :</label>
            <textarea name="about_me" class="form-control"></textarea>
        </div>

        <button type="submit" class="btn btn-success mt-3">Enregistrer</button>
    </form>
</div>

@endsection
