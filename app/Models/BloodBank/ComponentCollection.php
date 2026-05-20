<?php

namespace App\Models\BloodBank;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ComponentCollection extends Model
{
    protected $fillable = [
        'blood_group_id',
        'blood_collection_id',
        'donor_id',
        'component_id',
        'component_bag_no',
        'volume',
        'unit',
        'lot',
        'institution',
        'notes',
        'datetime',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'datetime' => 'datetime',
        'volume'   => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->component_bag_no)) {
                $prefix = 'CB-';
                $codes  = static::where('component_bag_no', 'like', $prefix . '%')->pluck('component_bag_no')->toArray();
                $max    = 0;
                foreach ($codes as $c) {
                    if (preg_match('/CB-(\d+)/', $c, $m)) {
                        $n = intval($m[1]);
                        if ($n > $max) {
                            $max = $n;
                        }
                    }
                }
                $next = $max ? $max + 1 : 101;
                $model->component_bag_no = $prefix . $next;
            }
        });
    }

    public function bloodGroup()
    {
        return $this->belongsTo(BloodGroup::class, 'blood_group_id');
    }

    public function bloodCollection()
    {
        return $this->belongsTo(BloodCollection::class, 'blood_collection_id');
    }

    public function donor()
    {
        return $this->belongsTo(BloodDonor::class, 'donor_id');
    }

    public function component()
    {
        return $this->belongsTo(Component::class, 'component_id');
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
