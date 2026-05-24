<?php

namespace App\Models\PatientPortal;

use App\Models\Patient;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class PatientPortalUser extends Model implements AuthenticatableContract
{
    use Authenticatable, Notifiable;

    protected $fillable = [
        'patient_id', 'email', 'phone', 'password',
        'mfa_enabled', 'mfa_secret',
        'phone_verified_at', 'email_verified_at', 'last_login_at',
        'locale', 'is_active',
    ];

    protected $hidden = ['password', 'mfa_secret', 'remember_token'];

    protected $casts = [
        'mfa_enabled' => 'boolean',
        'is_active' => 'boolean',
        'phone_verified_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function familyLinks()
    {
        return $this->hasMany(PatientFamilyLink::class, 'portal_user_id');
    }
}
