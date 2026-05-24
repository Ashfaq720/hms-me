<?php

namespace App\Models;

use App\Traits\LogPreference;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, HasApiTokens, LogPreference;

    protected $table = 'users';
    protected string $logName = 'user';

    // protected $guarded = ['id'];
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'type',
        'is_active',
        'current_organization_id',
        'current_branch_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function currentOrganization()
    {
        return $this->belongsTo(\App\Models\Organization\Organization::class, 'current_organization_id');
    }

    public function currentBranch()
    {
        return $this->belongsTo(\App\Models\Organization\Branch::class, 'current_branch_id');
    }

    public function branches()
    {
        return $this->belongsToMany(\App\Models\Organization\Branch::class, 'branch_user')
            ->withPivot('is_default')
            ->withTimestamps();
    }

    public function employee()
    {
        return $this->hasOne(\App\Models\Hr\Employee::class);
    }

    /** Link to the Doctor record when this user IS a doctor. */
    public function doctor()
    {
        return $this->hasOne(\App\Models\Doctor::class, 'user_id');
    }
}
