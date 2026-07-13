<?php

namespace App\Http\Controllers\ICU;

use App\Http\Controllers\Controller;
use App\Http\Requests\IcuDoctorOrderRequest;
use App\Models\Doctor;
use App\Models\Icu\IcuAdmission;
use App\Models\Icu\IcuDoctorOrder;
use App\Models\Icu\IcuOrderAuditLog;
use App\Models\Icu\IcuOrderExecutionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IcuDoctorOrderController extends Controller
{
    /**
     * Cross-admission Order Management dashboard.
     * Lists every order from icu_doctor_orders with filters, stats and quick-links to
     * the type-specific module (medicine / pathology / radiology / procedure).
     */
    public function manage(Request $request)
    {
        $status   = $request->input('status');
        $type     = $request->input('type');
        $priority = $request->input('priority');
        $dateKey  = $request->input('date', 'all'); // today|7d|30d|all
        $search   = trim((string) $request->input('q', ''));
        $icuType  = $request->input('icu_type'); // ICU | CCU (null = both)

        $base = IcuDoctorOrder::query()
            ->with(['patient:id,patient_name', 'doctor:id,name', 'admission:id,icu_case_id,icu_type']);

        if ($icuType) {
            $base->whereHas('admission', fn($q) => $q->where('icu_type', $icuType));
        }

        switch ($dateKey) {
            case '7d':
                $base->where('created_at', '>=', now()->subDays(7)->startOfDay());
                break;
            case '30d':
                $base->where('created_at', '>=', now()->subDays(30)->startOfDay());
                break;
            case 'all':
                break;
            case 'today':
            default:
                $base->whereDate('created_at', now()->toDateString());
                break;
        }

        // Stats reflect the active date scope (independent of other filters)
        $statsQuery = (clone $base);
        $stats = [
            'stat'      => (clone $statsQuery)->where('priority', 'STAT')->count(),
            'urgent'    => (clone $statsQuery)->where('priority', 'Urgent')->count(),
            'routine'   => (clone $statsQuery)->where('priority', 'Routine')->count(),
            'completed' => (clone $statsQuery)->where('status', 'Completed')->count(),
            'total'     => (clone $statsQuery)->count(),
        ];

        if ($status) {
            $base->where('status', $status);
        }
        if ($type) {
            $base->where('order_type', $type);
        }
        if ($priority) {
            $base->where('priority', $priority);
        }
        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('order_title', 'like', "%{$search}%")
                  ->orWhere('order_details', 'like', "%{$search}%")
                  ->orWhere('id', $search)
                  ->orWhereHas('doctor', fn($d) => $d->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('patient', fn($p) => $p->where('patient_name', 'like', "%{$search}%"));
            });
        }

        $orders = $base
            ->orderByRaw("FIELD(priority,'STAT','Urgent','Routine')")
            ->latest('id')
            ->get();

        return view('icu.orders.manage', compact(
            'orders', 'stats', 'status', 'type', 'priority', 'dateKey', 'search', 'icuType'
        ));
    }

    /**
     * Order detail page (Order Management drill-in).
     */
    public function show($orderId)
    {
        $order = IcuDoctorOrder::with([
            'patient',
            'doctor',
            'admission.patient',
            'executionLogs.executor:id,name',
            'auditLogs',
        ])->findOrFail($orderId);

        return view('icu.orders.show', compact('order'));
    }

    /**
     * Order queue for one ICU admission, sorted STAT > Urgent > Routine then newest first.
     */
    public function index($admissionId)
    {
        $admission = IcuAdmission::with('patient')->findOrFail($admissionId);

        $orders = IcuDoctorOrder::with(['doctor', 'executionLogs', 'auditLogs'])
            ->where('icu_admission_id', $admission->id)
            ->orderByRaw("FIELD(priority,'STAT','Urgent','Routine')")
            ->latest('id')
            ->get();

        $doctors = Doctor::select('id', 'name')->get();

        return view('icu.orders.index', compact('admission', 'orders', 'doctors'));
    }

    public function store(IcuDoctorOrderRequest $request, $admissionId)
    {
        DB::beginTransaction();
        try {
            $admission = IcuAdmission::findOrFail($admissionId);
            $this->guardAdmissionActive($admission);

            $data = $request->validated();

            $order = IcuDoctorOrder::create([
                'icu_admission_id'    => $admission->id,
                'icu_case_id'         => $admission->icu_case_id,
                'patient_id'          => $admission->patient_id,
                'doctor_id'           => $data['doctor_id'],
                'order_type'          => $data['order_type'],
                'order_title'         => $data['order_title'],
                'order_details'       => $data['order_details'] ?? null,
                'priority'            => $data['priority'],
                'start_time'          => $data['start_time'] ?? now(),
                'frequency'           => $data['frequency'] ?? null,
                'duration'            => $data['duration'] ?? null,
                'requires_doctor_ack' => $data['requires_doctor_ack'] ?? false,
                'status'              => 'Ordered',
                'remarks'             => $data['remarks'] ?? null,
                'created_by'          => auth()->id(),
            ]);

            $this->writeAudit($order, 'Created', null, $order->only([
                'order_type', 'order_title', 'order_details', 'priority', 'frequency', 'duration',
            ]));

            DB::commit();
            return back()->with('success', 'Order created.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('ICU order create failed', ['message' => $e->getMessage()]);
            return back()->with('error', 'Order create failed: ' . $e->getMessage());
        }
    }

    public function acknowledge(Request $request, $admissionId, $orderId)
    {
        return $this->transition($admissionId, $orderId, 'Acknowledged');
    }

    public function start(Request $request, $admissionId, $orderId)
    {
        return $this->transition($admissionId, $orderId, 'InProgress');
    }

    public function complete(Request $request, $admissionId, $orderId)
    {
        return $this->transition($admissionId, $orderId, 'Completed');
    }

    public function cancel(Request $request, $admissionId, $orderId)
    {
        $request->validate(['reason' => ['required', 'string', 'max:500']]);
        return $this->transition($admissionId, $orderId, 'Cancelled', $request->reason);
    }

    /**
     * Doctor reviews a completed order that requires acknowledgement.
     */
    public function doctorAcknowledge(Request $request, $admissionId, $orderId)
    {
        DB::beginTransaction();
        try {
            $order = $this->loadOrder($admissionId, $orderId);

            if (! $order->requires_doctor_ack) {
                throw new \RuntimeException('Order does not require doctor acknowledgement.');
            }
            if ($order->doctor_acknowledged_at) {
                throw new \RuntimeException('Order is already acknowledged.');
            }

            $order->doctor_acknowledged_at = now();
            $order->doctor_acknowledged_by = auth()->id();
            $order->save();

            $this->writeAudit($order, 'DoctorAcknowledged', null, ['note' => $request->input('note')]);

            DB::commit();
            return back()->with('success', 'Doctor acknowledgement recorded.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function modify(Request $request, $admissionId, $orderId)
    {
        $request->validate([
            'order_title'   => ['required', 'string', 'max:255'],
            'order_details' => ['nullable', 'string', 'max:4000'],
            'frequency'     => ['nullable', 'string', 'max:50'],
            'duration'      => ['nullable', 'string', 'max:50'],
            'priority'      => ['required', 'in:Routine,Urgent,STAT'],
            'reason'        => ['required', 'string', 'max:500'],
        ]);

        DB::beginTransaction();
        try {
            $order = $this->loadOrder($admissionId, $orderId);

            if (in_array($order->status, ['Completed', 'Cancelled'])) {
                throw new \RuntimeException("Cannot modify a {$order->status} order.");
            }

            $old = $order->only(['order_title', 'order_details', 'frequency', 'duration', 'priority']);

            $order->fill($request->only(['order_title', 'order_details', 'frequency', 'duration', 'priority']));
            $order->status = 'Modified';
            $order->save();

            $this->writeAudit($order, 'Modified', $old, $order->only(array_keys($old)), $request->reason);

            DB::commit();
            return back()->with('success', 'Order modified (audit trail recorded).');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    // -------- Helpers --------

    protected function transition($admissionId, $orderId, string $next, ?string $reason = null)
    {
        DB::beginTransaction();
        try {
            $order = $this->loadOrder($admissionId, $orderId);

            $allowed = match ($next) {
                'Acknowledged' => ['Ordered'],
                'InProgress'   => ['Ordered', 'Acknowledged', 'OnHold'],
                'Completed'    => ['InProgress', 'Acknowledged'],
                'Cancelled'    => ['Ordered', 'Acknowledged', 'InProgress', 'OnHold', 'Modified'],
                default        => [],
            };

            if (! in_array($order->status, $allowed, true)) {
                throw new \RuntimeException("Cannot transition from {$order->status} to {$next}.");
            }

            $oldStatus     = $order->status;
            $order->status = $next;
            $order->save();

            IcuOrderExecutionLog::create([
                'order_id'             => $order->id,
                'status'               => $next,
                'executed_by'          => auth()->id(),
                'execution_start_time' => $next === 'InProgress' ? now() : null,
                'execution_end_time'   => $next === 'Completed'  ? now() : null,
                'remarks'              => $reason,
                'created_at'           => now(),
            ]);

            $this->writeAudit($order, $next, ['status' => $oldStatus], ['status' => $next], $reason);

            DB::commit();
            return back()->with('success', "Order moved to {$next}.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    protected function loadOrder($admissionId, $orderId): IcuDoctorOrder
    {
        $order = IcuDoctorOrder::lockForUpdate()->findOrFail($orderId);
        if ($order->icu_admission_id != $admissionId) {
            throw new \RuntimeException('Order does not belong to this admission.');
        }
        return $order;
    }

    protected function guardAdmissionActive(IcuAdmission $admission): void
    {
        if (! in_array($admission->status, ['Approved', 'Admitted'])) {
            throw new \RuntimeException("ICU admission is not active (status: {$admission->status}).");
        }
    }

    protected function writeAudit(IcuDoctorOrder $order, string $action, ?array $old, ?array $new, ?string $reason = null): void
    {
        IcuOrderAuditLog::create([
            'order_id'   => $order->id,
            'action_type' => $action,
            'old_value'  => $old,
            'new_value'  => $new,
            'reason'     => $reason,
            'changed_by' => auth()->id(),
            'changed_at' => now(),
            'created_at' => now(),
        ]);
    }
}
