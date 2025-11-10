<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DewormTreatment extends Model
{
    use HasFactory;

    protected $fillable = [
        'deworming_id',
        'treatment',
    ];

    public function deworming()
    {
        return $this->belongsTo(Deworming::class);
    }
}
