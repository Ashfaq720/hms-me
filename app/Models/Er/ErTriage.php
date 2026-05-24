<?php

namespace App\Models\Er;

use App\Models\FrontDesk\ErPatient;
use Illuminate\Database\Eloquent\Model;

class ErTriage extends Model
{
    protected $table = 'er_triages';
    protected $guarded = [];

    protected $casts = [
        'triaged_at' => 'datetime',
    ];

    public function erPatient() { return $this->belongsTo(ErPatient::class); }

    public static function levelColour(string $level): string
    {
        return [
            'RED'    => 'danger',
            'ORANGE' => 'warning',
            'YELLOW' => 'info',
            'GREEN'  => 'success',
            'BLACK'  => 'dark',
        ][$level] ?? 'secondary';
    }
}
