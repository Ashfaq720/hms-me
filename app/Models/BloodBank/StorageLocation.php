<?php
namespace App\Models\BloodBank;

use Illuminate\Database\Eloquent\Model;

class StorageLocation extends Model
{
    protected $fillable = [
        'location_code', 'location_name', 'location_type',
        'capacity_units', 'temperature_monitoring_required',
        'device_id', 'status', 'created_by', 'updated_by',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->location_code)) {
                $prefix = 'LC-';
                $codes  = static::where('location_code', 'like', $prefix . '%')->pluck('location_code')->toArray();
                $max    = 0;
                foreach ($codes as $c) {
                    if (preg_match('/LC-(\d+)/', $c, $m)) {
                        $n = intval($m[1]);
                        if ($n > $max) {
                            $max = $n;
                        }
                    }
                }
                $next        = $max ? $max + 1 : 101;
                $model->location_code = $prefix . $next;
            }
        });
    }

    protected $casts = [
        'capacity_units'                  => 'integer',
        'temperature_monitoring_required' => 'boolean',
    ];
}
