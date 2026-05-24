<?php

namespace App\Modules\Ambulance\Models;

use Illuminate\Database\Eloquent\Model;

class AssignmentAudit extends Model
{
    protected $table = 'amb_assignment_audits';
    public $timestamps = false;
    protected $guarded = [];
}
