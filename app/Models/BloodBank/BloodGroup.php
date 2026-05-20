<?php
namespace App\Models\BloodBank;

use Illuminate\Database\Eloquent\Model;

class BloodGroup extends Model
{
    protected $fillable = [
        'code', 'abo_group', 'rh_factor', 'display_name',
        'is_active', 'is_locked', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_locked' => 'boolean',
    ];
    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->code)) {
                $prefix = 'BG-';
                $codes  = static::where('code', 'like', $prefix . '%')->pluck('code')->toArray();
                $max    = 0;
                foreach ($codes as $c) {
                    if (preg_match('/BG-(\d+)/', $c, $m)) {
                        $n = intval($m[1]);
                        if ($n > $max) {
                            $max = $n;
                        }
                    }
                }
                $next        = $max ? $max + 1 : 101;
                $model->code = $prefix . $next;
            }
        });
    }
    public function getCombinedAttribute(): string
    {
        return $this->abo_group . ($this->rh_factor === 'POS' ? '+' : '-');
    }
}
