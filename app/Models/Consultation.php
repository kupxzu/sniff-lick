<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Consultation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'pet_id',
        'consultation_date',
        'weight',
        'temperature',
        'complaint',
        'diagnosis',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'consultation_date' => 'datetime',
        'weight' => 'decimal:2',
        'temperature' => 'decimal:2',
    ];

    /**
     * Get the pet that this consultation belongs to.
     */
    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    /**
     * Get the lab tests for this consultation.
     */
    public function labtests(): HasMany
    {
        return $this->hasMany(Labtest::class);
    }

    /**
     * Get the treatments for this consultation.
     */
    public function treatments(): HasMany
    {
        return $this->hasMany(Treatment::class);
    }

    /**
     * Get the prescriptions for this consultation.
     */
    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }
}
