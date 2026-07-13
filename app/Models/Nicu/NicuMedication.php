<?php

namespace App\Models\Nicu;

use App\Models\Doctor;
use App\Models\NicuAdmission;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class NicuMedication extends Model
{
    protected $table = 'nicu_medications';

    public const ROUTES = ['IV', 'IM', 'PO', 'SC', 'PR', 'Inhaled', 'Topical', 'NG'];

    protected $fillable = [
        'nicu_admission_id', 'administered_at',
        'drug_name', 'dose', 'route', 'frequency',
        'indication', 'notes',
        'prescribed_by', 'administered_by',
    ];

    protected $casts = [
        'administered_at' => 'datetime',
    ];

    public function admission()
    {
        return $this->belongsTo(NicuAdmission::class, 'nicu_admission_id');
    }

    public function prescriber()
    {
        return $this->belongsTo(Doctor::class, 'prescribed_by');
    }

    public function administeredBy()
    {
        return $this->belongsTo(User::class, 'administered_by');
    }
}
