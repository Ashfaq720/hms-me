<?php

namespace App\Models\Ipd;

use App\Models\LabInvestigation;
use App\Models\LabInvestigationCategory;
use App\Models\LabInvestigationType;
use Illuminate\Database\Eloquent\Model;

class LabInvestigationOrderRequest extends Model
{
    protected $table = 'lab_investigation_order_request';

    protected $fillable = [
        'lab_inv_order_id',
        'lab_inv_id',
        'lab_inv_type_id',
        'lab_inv_category_id',
        'status',
        'file',
    ];

    public function order()
    {
        return $this->belongsTo(LabInvestigationOrder::class, 'lab_inv_order_id');
    }

    public function labInvestigation()
    {
        return $this->belongsTo(LabInvestigation::class, 'lab_inv_id');
    }

    public function labInvestigationType()
    {
        return $this->belongsTo(LabInvestigationType::class, 'lab_inv_type_id');
    }

    public function labInvestigationCategory()
    {
        return $this->belongsTo(LabInvestigationCategory::class, 'lab_inv_category_id');
    }
}
