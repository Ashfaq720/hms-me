<?php

namespace App\Models\Ot;

use Illuminate\Database\Eloquent\Model;

class OtNotification extends Model
{
    protected $table = 'ot_notifications';

    protected $fillable = [
        'user_id', 'role', 'type', 'title', 'body',
        'entity_type', 'entity_id', 'action_url', 'severity', 'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public static function dispatch(array $payload): self
    {
        return self::create(array_merge([
            'severity' => 'info',
        ], $payload));
    }

    public function scopeUnread($q)
    {
        return $q->whereNull('read_at');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /**
     * Resolve the related entity using entity_type as a slug.
     * (Same approach as OtAuditLog::resolveEntity().)
     */
    public function resolveEntity()
    {
        $map = [
            'surgery_request'   => OtSurgeryRequest::class,
            'surgery_schedule'  => OtSurgerySchedule::class,
            'ot_consumable'     => OtConsumable::class,
            'ot_room'           => OtRoom::class,
            'pacu_record'       => OtPacuRecord::class,
            'pre_op_checklist'  => OtPreOpChecklist::class,
            'ot_transfer'       => OtTransfer::class,
            'consumable_usage'  => OtConsumableUsage::class,
        ];
        $cls = $map[$this->entity_type] ?? null;
        return ($cls && $this->entity_id) ? $cls::find($this->entity_id) : null;
    }
}
