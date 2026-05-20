<?php

namespace App\Models;

use App\Models\Pharmacy\Medicine;
use Illuminate\Database\Eloquent\Model;

class PresciptionMedicine extends Model
{
    protected $fillable = [
        'prescription_id',
        'medicine_id',
        'dosage',
        'frequency',
        'duration',
        'note',
    ];

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }
    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }
}
