<?php

namespace App\Models\Ipd;

use App\Models\Doctor;
use App\Models\MasterData\Operation;
use App\Models\MasterData\OperationProcedure;
use App\Models\MasterData\OperationTheatre;
use App\Models\MasterData\OperationType;
use Illuminate\Database\Eloquent\Model;

class OperationHistory extends Model
{
    protected $fillable = [
        'case_id',
        'opd_id',
        'ipd_id',
        'er_id',
        'customer_type',
        'operation_type_id',
        'operation_id',
        'operation_procedure_id',
        'operation_theatre_id',
        'date',
        'start_datetime',
        'end_datetime',
        'pre_op',
        'vitals',
        'consent',
        'equipment',
        'diagnosis',
        'assign_doctor_id',
        'assistant_doctor_id',
        'main_surgeon_id',
        'anesthesiologist_id',
        'ot_technician',
        'remarks',
        'status',
    ];

    protected $casts = [
        'date'           => 'date',
        'start_datetime' => 'datetime',
        'end_datetime'   => 'datetime',
        'pre_op'         => 'boolean',
        'vitals'         => 'boolean',
        'consent'        => 'boolean',
        'equipment'      => 'boolean',
    ];

    public function operationType()
    {
        return $this->belongsTo(OperationType::class);
    }

    public function operation()
    {
        return $this->belongsTo(Operation::class);
    }

    public function operationProcedure()
    {
        return $this->belongsTo(OperationProcedure::class);
    }

    public function operationTheatre()
    {
        return $this->belongsTo(OperationTheatre::class);
    }

    public function assignDoctor()
    {
        return $this->belongsTo(Doctor::class, 'assign_doctor_id');
    }

    public function assistantDoctor()
    {
        return $this->belongsTo(Doctor::class, 'assistant_doctor_id');
    }

    public function mainSurgeon()
    {
        return $this->belongsTo(Doctor::class, 'main_surgeon_id');
    }

    public function anesthesiologist()
    {
        return $this->belongsTo(Doctor::class, 'anesthesiologist_id');
    }
}
