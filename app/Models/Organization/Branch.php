<?php

namespace App\Models\Organization;

use App\Models\User;
use App\Traits\LogPreference;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use SoftDeletes, LogPreference;

    protected string $logName = 'branch';

    protected $fillable = [
        'organization_id', 'code', 'name', 'type',
        'address_line1', 'address_line2', 'city', 'state',
        'postal_code', 'country', 'phone', 'email',
        'mrn_prefix', 'invoice_prefix', 'health_card_prefix',
        'is_active', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'branch_user')
            ->withPivot('is_default')
            ->withTimestamps();
    }
}
