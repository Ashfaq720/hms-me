<?php

namespace App\Models\Ipd;

use Illuminate\Database\Eloquent\Model;

class IpdNurseNoteReply extends Model
{
    protected $fillable = [
        'ipd_nurse_note_id',
        'user_id',
        'user_name',
        'user_role',
        'reply',
    ];

    public function nurseNote()
    {
        return $this->belongsTo(IpdNurseNote::class, 'ipd_nurse_note_id');
    }
}
