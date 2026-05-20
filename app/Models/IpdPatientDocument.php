<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpdPatientDocument extends Model
{
    protected $table = 'ipd_patient_documents';

    protected $fillable = [
        'ipd_patient_id',
        'title',
        'file',
        'remarks',
    ];

    public function ipdPatient()
    {
        return $this->belongsTo(IpdPatient::class, 'ipd_patient_id');
    }
}
