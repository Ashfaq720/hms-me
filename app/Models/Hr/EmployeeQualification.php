<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Model;

class EmployeeQualification extends Model
{
    protected $fillable = ['employee_id', 'degree', 'institution', 'year', 'document_path'];
}
