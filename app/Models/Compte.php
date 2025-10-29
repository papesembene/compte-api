<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Modèle représentant un compte bancaire.
 *
 * Responsabilité : Gérer les données et relations du compte.
 */
class Compte extends Model
{
    use HasFactory, SoftDeletes;

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
        'type_compte',
        'statut',
        'client_id',
        'date_debut_blocage',
        'date_fin_blocage',
    ];

    /**
     * Champs visibles dans les réponses JSON (le solde est calculé dynamiquement).
     */
    protected $appends = [
        'solde',
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

    /**
     * Relation avec les transactions du compte.
     *
     * @return HasMany
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Accesseur pour calculer le solde dynamiquement.
     *
     * @return float
     */
    public function getSoldeAttribute(): float
    {
        return $this->transactions()
            ->where('statut', 'termine')
            ->sum(DB::raw("CASE WHEN type = 'depot' THEN montant ELSE -montant END"));
    }


    /**
     * Scope global pour récupérer les comptes non supprimés (non soft deleted).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNonSupprime($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Scope local pour récupérer un compte par son numéro.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $numero
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNumero($query, $numero)
    {
        return $query->where('numero_compte', $numero);
    }


    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }

            if (empty($model->numero_compte)) {
                $model->numero_compte = static::generateNumeroCompte();
            }
        });
    }

    /**
     * Génère un numéro de compte unique.
     *
     * @return string
     */
    public static function generateNumeroCompte(): string
    {
        do {
            $numero = str_pad(mt_rand(1, 9999999999), 10, '0', STR_PAD_LEFT);
        } while (static::where('numero_compte', $numero)->exists());

        return $numero;
    }
}
