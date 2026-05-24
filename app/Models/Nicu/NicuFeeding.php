<?php

namespace App\Models\Nicu;

use App\Models\NicuAdmission;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class NicuFeeding extends Model
{
    protected $table = 'nicu_feedings';

    public const FEED_TYPES = ['Breast', 'Formula', 'EBM', 'TPN', 'NG', 'OG'];
    public const ROUTES     = ['Oral', 'NG', 'OG', 'IV'];

    protected $fillable = [
        'nicu_admission_id', 'fed_at',
        'feed_type', 'route', 'volume_ml',
        'tolerated', 'vomited',
        'notes', 'recorded_by',
    ];

    protected $casts = [
        'fed_at'    => 'datetime',
        'volume_ml' => 'decimal:1',
        'tolerated' => 'boolean',
        'vomited'   => 'boolean',
    ];

    public function admission()
    {
        return $this->belongsTo(NicuAdmission::class, 'nicu_admission_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
