<?php
namespace App\Models\BloodBank;

use Illuminate\Database\Eloquent\Model;

class BloodDonor extends Model
{
    protected $fillable = [
        'donor_code', 'name', 'dob', 'blood_group_id', 'gender',
        'father_name', 'contact_no', 'address',
        'is_active', 'created_by', 'updated_by',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->donor_code)) {
                $prefix = 'BD-';
                $codes  = static::where('donor_code', 'like', $prefix . '%')->pluck('donor_code')->toArray();
                $max    = 0;
                foreach ($codes as $c) {
                    if (preg_match('/BD-(\d+)/', $c, $m)) {
                        $n = intval($m[1]);
                        if ($n > $max) {
                            $max = $n;
                        }
                    }
                }
                $next = $max ? $max + 1 : 101;
                $model->donor_code = $prefix . $next;
            }
        });
    }

    protected $casts = [
        'dob'       => 'date',
        'is_active' => 'boolean',
    ];

    public function bloodGroup()
    {
        return $this->belongsTo(BloodGroup::class, 'blood_group_id');
    }
}
