<?php

namespace App\Models\Ot;

use Illuminate\Database\Eloquent\Model;

class OtAuditLog extends Model
{
    protected $table = 'ot_audit_logs';

    protected $fillable = [
        'entity_type', 'entity_id', 'action', 'from_status', 'to_status',
        'reason', 'payload', 'user_id', 'ip_address',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /**
     * entity_type stores a string slug like 'surgery_request' / 'surgery_schedule'.
     * To resolve the related model dynamically, use this helper rather than
     * a true polymorphic morphTo() because the stored types are short slugs,
     * not class names.
     */
    public function resolveEntity()
    {
        $map = [
            'surgery_request'   => OtSurgeryRequest::class,
            'surgery_schedule'  => OtSurgerySchedule::class,
            'surgery_team'      => OtSurgeryTeam::class,
            'pre_op_checklist'  => OtPreOpChecklist::class,
            'ot_transfer'       => OtTransfer::class,
            'anesthesia_record' => OtAnesthesiaRecord::class,
            'intra_op_record'   => OtIntraOpRecord::class,
            'post_op_note'      => OtPostOpNote::class,
            'pacu_record'       => OtPacuRecord::class,
            'consumable_usage'  => OtConsumableUsage::class,
            'ot_cleaning'       => OtCleaningLog::class,
            'ot_document'       => OtDocument::class,
            'ot_room'           => OtRoom::class,
        ];
        $cls = $map[$this->entity_type] ?? null;
        return ($cls && $this->entity_id) ? $cls::find($this->entity_id) : null;
    }

    public static function record(
        string $entityType,
        int $entityId,
        string $action,
        ?string $fromStatus = null,
        ?string $toStatus = null,
        ?string $reason = null,
        ?array $payload = null
    ): self {
        return self::create([
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'reason' => $reason,
            'payload' => $payload,
            'user_id' => auth()->id(),
            'ip_address' => request()?->ip(),
        ]);
    }
}
