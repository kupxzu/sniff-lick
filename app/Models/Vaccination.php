<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vaccination extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'pet_id',
        'date',
        'weight',
        'temperature',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'datetime',
        'weight' => 'decimal:2',
        'temperature' => 'decimal:2',
    ];

    /**
     * Set the weight attribute, converting empty strings to null
     *
     * @param mixed $value
     * @return void
     */
    public function setWeightAttribute($value)
    {
        $this->attributes['weight'] = ($value === '' || $value === null) ? null : $value;
    }

    /**
     * Set the temperature attribute, converting empty strings to null
     *
     * @param mixed $value
     * @return void
     */
    public function setTemperatureAttribute($value)
    {
        $this->attributes['temperature'] = ($value === '' || $value === null) ? null : $value;
    }

    /**
     * Get the pet that this vaccination belongs to.
     */
    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    /**
     * Get the vac treatments for this vaccination.
     */
    public function vacTreatments(): HasMany
    {
        return $this->hasMany(VacTreatment::class, 'vac_id');
    }
}
