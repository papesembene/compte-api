<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Laravel\Passport\HasApiTokens;

/**
 * Modèle représentant un client de la banque.
 *
 * Responsabilité : Gérer les données et relations du client.
 */
class Client extends Model implements \Illuminate\Contracts\Auth\Authenticatable
{
    use HasFactory, HasApiTokens, \Illuminate\Auth\Authenticatable;

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
        'password',
        'code',
    ];

    /**
     * Champs cachés dans les réponses JSON.
     */
    protected $hidden = [
        'password',
        'code',
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

    /**
     * Génère un mot de passe aléatoire.
     *
     * @return string
     */
    public static function generatePassword(): string
    {
        return Str::random(12); // 12 caractères aléatoires
    }

    /**
     * Génère un code de 6 chiffres.
     *
     * @return string
     */
    public static function generateCode(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
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

            // Générer password et code si non fournis
            if (empty($model->password)) {
                $model->password = bcrypt(self::generatePassword());
            }

            if (empty($model->code)) {
                $model->code = self::generateCode();
            }
        });
    }
}
