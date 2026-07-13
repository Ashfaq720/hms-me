<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabInvestigation extends Model
{
    protected $fillable = [
        'name', 'short_name', 'category_id', 'department', 'sample_type',
        'report_time_hours', 'normal_range', 'unit', 'method',
        'preparation', 'description', 'price', 'sort_order', 'notes', 'status',
    ];

    public function category()
    {
        return $this->belongsTo(LabInvestigationCategory::class, 'category_id');
    }
}
