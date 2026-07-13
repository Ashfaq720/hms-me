<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresciptionLabInvestigation extends Model
{
    protected $fillable = [
        'prescription_id',
        'lab_investigation_id',
        'note',
    ];

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }

    public function labInvestigation()
    {
        return $this->belongsTo(LabInvestigation::class);
    }
}
