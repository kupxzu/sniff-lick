<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pet extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'client_id',
        'name',
        'age',
        'species',
        'breed',
        'colormark',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'age' => 'integer',
    ];

    /**
     * Get the client that owns the pet.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Legacy method - Get the user that owns the pet.
     */
    public function user(): BelongsTo
    {
        return $this->client();
    }

    /**
     * Get the consultations for this pet.
     */
    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class);
    }

    /**
     * Get the vaccinations for this pet.
     */
    public function vaccinations(): HasMany
    {
        return $this->hasMany(Vaccination::class);
    }
}
