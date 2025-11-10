<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VacTreatment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vac_id',
        'treatment',
        'dose',
    ];

    /**
     * Get the vaccination that this treatment belongs to.
     */
    public function vaccination(): BelongsTo
    {
        return $this->belongsTo(Vaccination::class, 'vac_id');
    }
}
