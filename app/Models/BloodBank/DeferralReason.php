<?php
namespace App\Models\BloodBank;

use Illuminate\Database\Eloquent\Model;

class DeferralReason extends Model
{
    protected $fillable = [
        'deferral_deferral_code', 'deferral_reason', 'deferral_type',
        'default_duration_days', 'regulatory_reference',
        'is_active', 'is_locked', 'created_by', 'updated_by',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->deferral_code)) {
                $prefix = 'DR-';
                $deferral_codes  = static::where('deferral_code', 'like', $prefix . '%')->pluck('deferral_code')->toArray();
                $max    = 0;
                foreach ($deferral_codes as $c) {
                    if (preg_match('/DR-(\d+)/', $c, $m)) {
                        $n = intval($m[1]);
                        if ($n > $max) {
                            $max = $n;
                        }
                    }
                }
                $next        = $max ? $max + 1 : 101;
                $model->deferral_code = $prefix . $next;
            }
        });
    }

    protected $casts = [
        'default_duration_days' => 'integer',
        'is_active'             => 'boolean',
        'is_locked'             => 'boolean',
    ];
}
