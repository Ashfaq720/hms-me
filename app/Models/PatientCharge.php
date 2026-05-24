<?php
namespace App\Models;

use App\Models\Charges\Charge;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientCharge extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'case_id',
        'charge_module',
        'doctor_id',
        'department_id',
        'ipd_id',
        'opd_id',
        'appointment_id',
        'er_register_id',
        'pathology_id',
        'radiology_id',
        'blood_bank_id',
        'pharmacy_id',
        'charge_item',
        'charge_id',
        'unit_price',
        'quantity',
        'amount',
        'vat',
        'tax',
        'net_amount',
        'date',
        'notes',
        'files',
        'remarks',
        'status',
        'is_paid',
        'is_bill_generated',
        'created_by',
        'deleted_by',
    ];

    protected $casts = [
        'date'              => 'datetime',
        'unit_price'        => 'decimal:2',
        'quantity'          => 'integer',
        'amount'            => 'decimal:2',
        'vat'               => 'decimal:2',
        'tax'               => 'decimal:2',
        'net_amount'        => 'decimal:2',
        'is_paid'           => 'boolean',
        'is_bill_generated' => 'boolean',
    ];


    public function charge()
    {
        return $this->belongsTo(Charge::class, 'charge_id');
    }

}
