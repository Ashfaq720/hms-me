<?php

namespace App\Models\Ot;

use Illuminate\Database\Eloquent\Model;

/**
 * Anesthesia record for a single surgery schedule.
 *
 * Field shape conventions (read by views and the inline summary card on
 * the surgery-request show page):
 *
 *   pre_anesthesia_assessment, drugs_used, airway_management,
 *   complications, post_anesthesia_notes
 *       → plain free-text. Multiline allowed (rendered in <pre> with
 *         white-space: pre-wrap). Never store JSON or structured data
 *         in these columns.
 *
 *   intra_op_vitals
 *       → ARRAY (cast to array). Each row is an associative array:
 *         ['t' => '09:10', 'bp' => '120/80', 'hr' => 80, 'spo2' => 99]
 *         (key 't' is the timestamp; 'time' is also accepted by the
 *         renderer for backward compatibility).
 *
 *   asa_grade
 *       → short string, e.g. "ASA II".
 */
class OtAnesthesiaRecord extends Model
{
    protected $table = 'ot_anesthesia_records';

    protected $fillable = [
        'surgery_schedule_id', 'anesthesia_type_id', 'anesthetist_id',
        'induction_time', 'recovery_time', 'pre_anesthesia_assessment',
        'drugs_used', 'airway_management', 'intra_op_vitals', 'complications',
        'post_anesthesia_notes', 'asa_grade',
    ];

    protected $casts = [
        'induction_time' => 'datetime',
        'recovery_time' => 'datetime',
        'intra_op_vitals' => 'array',
    ];

    public function schedule()
    {
        return $this->belongsTo(OtSurgerySchedule::class, 'surgery_schedule_id');
    }

    public function anesthesiaType()
    {
        return $this->belongsTo(OtAnesthesiaType::class);
    }

    public function anesthetist()
    {
        return $this->belongsTo(\App\Models\User::class, 'anesthetist_id');
    }
}
