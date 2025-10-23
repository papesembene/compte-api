<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle représentant un client de la banque.
 *
 * Responsabilité : Gérer les données et relations du client.
 */
class Client extends Model
{
    use HasFactory;

    /**
     * Indique que la clé primaire n'est pas auto-incrémentée.
     */
    public $incrementing = false;

    /**
     * Type de la clé primaire : string (UUID).
     */
    protected $keyType = 'string';

    /**
     * Champs remplissables pour la création/mise à jour.
     */
    protected $fillable = [
        'id',
        'titulaire',
        'nci',
        'email',
        'telephone',
        'adresse',
    ];

    /**
     * Relation avec les comptes du client.
     *
     * @return HasMany
     */
    public function comptes(): HasMany
    {
        return $this->hasMany(Compte::class);
    }
}
