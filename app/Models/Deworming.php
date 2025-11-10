<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deworming extends Model
{
    use HasFactory;

    protected $fillable = [
        'pet_id',
        'date',
        'weight',
        'temperature',
    ];

    protected $casts = [
        'date' => 'datetime',
        'weight' => 'decimal:2',
        'temperature' => 'decimal:2',
    ];

    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }

    public function dewormTreatments()
    {
        return $this->hasMany(DewormTreatment::class);
    }
}
