<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle représentant un compte bancaire.
 *
 * Responsabilité : Gérer les données et relations du compte.
 */
class Compte extends Model
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
        'numero_compte',
        'solde',
        'type_compte',
        'statut',
        'client_id',
    ];

    /**
     * Relation avec le client propriétaire du compte.
     *
     * @return BelongsTo
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
