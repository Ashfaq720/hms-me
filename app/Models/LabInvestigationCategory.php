<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabInvestigationCategory extends Model
{
    protected $fillable = ['name', 'type_id', 'notes', 'status'];

    public function type()
    {
        return $this->belongsTo(LabInvestigationType::class, 'type_id');
    }
}
