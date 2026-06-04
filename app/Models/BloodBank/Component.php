<?php
namespace App\Models\BloodBank;

use Illuminate\Database\Eloquent\Model;

class Component extends Model
{
    protected $fillable = [
        'component_code', 'component_name', 'derived_from',
        'shelf_life_value', 'shelf_life_unit',
        'storage_requirement',
        'min_volume_ml', 'max_volume_ml',
        'is_active', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'is_active'        => 'boolean',
        'shelf_life_value' => 'integer',
        'min_volume_ml'    => 'integer',
        'max_volume_ml'    => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->component_code)) {
                $prefix = 'CMP-';
                $codes  = static::where('component_code', 'like', $prefix . '%')->pluck('component_code')->toArray();
                $max    = 0;
                foreach ($codes as $c) {
                    if (preg_match('/CMP-(\d+)/', $c, $m)) {
                        $n = intval($m[1]);
                        if ($n > $max) {
                            $max = $n;
                        }
                    }
                }
                $next        = $max ? $max + 1 : 101;
                $model->component_code = $prefix . $next;
            }
        });
    }

    public function temperatureRule()
    {
        return $this->hasOne(ComponentTemperatureRule::class, 'component_id');
    }

    public function bloodBags()
    {
        return $this->belongsToMany(
            BloodBag::class,
            'components',
            'component_id',
            'blood_bag_id'
        );
    }
}
