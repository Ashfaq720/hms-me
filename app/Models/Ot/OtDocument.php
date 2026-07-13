<?php

namespace App\Models\Ot;

use Illuminate\Database\Eloquent\Model;

class OtDocument extends Model
{
    protected $table = 'ot_documents';

    protected $fillable = [
        'surgery_request_id', 'surgery_schedule_id', 'document_type', 'title',
        'file_path', 'mime_type', 'uploaded_by', 'is_signed', 'signed_at', 'notes',
    ];

    protected $casts = [
        'is_signed' => 'boolean',
        'signed_at' => 'datetime',
    ];

    public function surgeryRequest()
    {
        return $this->belongsTo(OtSurgeryRequest::class, 'surgery_request_id');
    }

    public function schedule()
    {
        return $this->belongsTo(OtSurgerySchedule::class, 'surgery_schedule_id');
    }

    public function uploadedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'uploaded_by');
    }
}
