<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresciptionSymptom extends Model
{
    protected $fillable = [
        'prescription_id',
        'symptom_id',
        'note',
    ];

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }

    public function symptom()
    {
        return $this->belongsTo(Symptom::class);
    }
}
