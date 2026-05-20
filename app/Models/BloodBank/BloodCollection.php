<?php

namespace App\Models\BloodBank;

use Illuminate\Database\Eloquent\Model;
use App\Models\Charges\Charge;
use App\Models\User;

class BloodCollection extends Model
{
    protected $fillable = [
        'donor_id',
        'blood_group_id',
        'donate_date',
        'bag_no',
        'volume',
        'unit',
        'lot',
        'charge_id',
        'charge_name',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'donate_date' => 'datetime',
        'volume'      => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->bag_no)) {
                $prefix = 'BAG-';
                $codes  = static::where('bag_no', 'like', $prefix . '%')->pluck('bag_no')->toArray();
                $max    = 0;
                foreach ($codes as $c) {
                    if (preg_match('/BAG-(\d+)/', $c, $m)) {
                        $n = intval($m[1]);
                        if ($n > $max) {
                            $max = $n;
                        }
                    }
                }
                $next = $max ? $max + 1 : 101;
                $model->bag_no = $prefix . $next;
            }
        });
    }

    public function donor()
    {
        return $this->belongsTo(BloodDonor::class, 'donor_id');
    }

    public function bloodGroup()
    {
        return $this->belongsTo(BloodGroup::class, 'blood_group_id');
    }

    public function charge()
    {
        return $this->belongsTo(Charge::class, 'charge_id');
    }

    public function componentCollections()
    {
        return $this->hasMany(ComponentCollection::class, 'blood_collection_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
