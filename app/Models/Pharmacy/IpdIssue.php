<?php
namespace App\Models\Pharmacy;

use App\Models\IpdPatient;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class IpdIssue extends Model
{
    protected $fillable = [
        'issue_no',
        'ipd_patient_id',
        'patient_id',
        'requisition_no',
        'ward_bed',
        'request_source',
        'issued_by',
        'drug_count',
        'total_amount',
        'status',
        'note',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    public function ipdPatient()
    {
        return $this->belongsTo(IpdPatient::class, 'ipd_patient_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function items()
    {
        return $this->hasMany(IpdIssueItem::class);
    }
}
