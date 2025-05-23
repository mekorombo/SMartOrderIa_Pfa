<?php

namespace App\Models;

use App\Models\User;
use App\Models\ProduitCommande;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Commande extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total',
    ];


    public function user()
{
    return $this->belongsTo(User::class);
}

public function produitCommandes()
{
    return $this->hasMany(ProduitCommande::class);
}

}
