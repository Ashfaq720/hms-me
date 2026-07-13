<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class CostCenter extends Model
{
    protected $fillable = ['organization_id', 'branch_id', 'code', 'name', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
}
