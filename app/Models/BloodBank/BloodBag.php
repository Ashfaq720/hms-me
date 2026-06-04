<?php
namespace App\Models\BloodBank;

use Illuminate\Database\Eloquent\Model;

class BloodBag extends Model
{
    protected $fillable = [
        'bag_code', 'bag_type', 'volume_ml',
        'is_active', 'is_locked', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'volume_ml' => 'integer',
        'is_active' => 'boolean',
        'is_locked' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->bag_code)) {
                $prefix = 'BB-';
                $codes  = static::where('bag_code', 'like', $prefix . '%')->pluck('bag_code')->toArray();
                $max    = 0;
                foreach ($codes as $c) {
                    if (preg_match('/BB-(\d+)/', $c, $m)) {
                        $n = intval($m[1]);
                        if ($n > $max) {
                            $max = $n;
                        }
                    }
                }
                $next            = $max ? $max + 1 : 101;
                $model->bag_code = $prefix . $next;
            }
        });
    }
    public function components()
    {
        return $this->belongsToMany(
            Component::class,
            'blood_bag_components',
            'blood_bag_id',
            'component_id'
        );
    }
}
