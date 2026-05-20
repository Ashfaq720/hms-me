<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Model;

class IpdIssueItem extends Model
{
    protected $fillable = [
        'ipd_issue_id',
        'medicine_id',
        'duration',
        'qty_required',
        'available_qty',
        'store',
    ];

    public function ipdIssue()
    {
        return $this->belongsTo(IpdIssue::class);
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }
}
