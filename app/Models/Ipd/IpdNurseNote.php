<?php

namespace App\Models\Ipd;

use Illuminate\Database\Eloquent\Model;

class IpdNurseNote extends Model
{
    protected $casts = [
        'date' => 'datetime',
    ];

    protected $fillable = [
        'ipd_patient_id',
        'title',
        'doctor_category',
        'shift',
        'doctor_id',
        'priority',
        'file',
        'date',
        'nurse_name',
        'note',
        'observations',
    ];

    public function doctor()
    {
        return $this->belongsTo(\App\Models\Doctor::class);
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(IpdNurseNoteReply::class, 'ipd_nurse_note_id')->orderBy('created_at');
    }
}
