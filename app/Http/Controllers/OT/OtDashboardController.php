<?php

namespace App\Http\Controllers\OT;

use App\Models\Ot\OtNotification;
use App\Models\Ot\OtPacuRecord;
use App\Models\Ot\OtRoom;
use App\Models\Ot\OtSurgeryRequest;
use App\Models\Ot\OtSurgerySchedule;
use App\Models\Ot\OtTransfer;
use App\Services\Ot\OtDashboardService;
use App\Services\Ot\OtRoomStateService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OtDashboardController extends OtBaseController
{
    public function __construct(
        protected OtDashboardService $dash,
        protected OtRoomStateService $roomStates,
    ) {}

    public function index(Request $request)
    {
        $this->gate('ot_dashboard_access');

        $today = Carbon::today();
        $endOfDay = Carbon::today()->endOfDay();

        $todaySchedules = OtSurgerySchedule::with([
                'surgeryRequest.patient', 'surgeryRequest.surgeryType', 'surgeryRequest.primarySurgeon',
                'room', 'preOpChecklist', 'anesthesiaRecord',
            ])
            ->whereBetween('scheduled_start', [$today, $endOfDay])
            ->whereNotIn('status', [OtSurgerySchedule::STATUS_CANCELLED])
            ->orderByDesc('emergency_fast_track')
            ->orderBy('scheduled_start')
            ->get();

        $runningSurgeries = OtSurgerySchedule::with([
                'surgeryRequest.patient', 'surgeryRequest.surgeryType',
                'room', 'anesthesiaRecord',
            ])
            ->whereIn('status', [
                OtSurgerySchedule::STATUS_ANESTHESIA_STARTED,
                OtSurgerySchedule::STATUS_SURGERY_RUNNING,
            ])
            ->orderBy('actual_start')
            ->get();

        $delayedCases = OtSurgerySchedule::with([
                'surgeryRequest.patient', 'room', 'preOpChecklist',
                'transfers', 'teamMembers',
            ])
            ->where('scheduled_start', '<', now())
            ->whereIn('status', [
                OtSurgerySchedule::STATUS_SCHEDULED,
                OtSurgerySchedule::STATUS_PRE_OP_PENDING,
                OtSurgerySchedule::STATUS_READY_FOR_OT,
                OtSurgerySchedule::STATUS_TRANSFER_STARTED,
            ])
            ->whereDate('scheduled_start', $today)
            ->orderByDesc('emergency_fast_track')
            ->orderBy('scheduled_start')
            ->get();

        $emergencyCases = OtSurgerySchedule::with(['surgeryRequest.patient', 'surgeryRequest.surgeryType', 'surgeryRequest.primarySurgeon', 'room'])
            ->where('emergency_fast_track', true)
            ->whereNotIn('status', [
                OtSurgerySchedule::STATUS_CLOSED,
                OtSurgerySchedule::STATUS_CANCELLED,
            ])
            ->orderBy('scheduled_start')
            ->get();

        $rooms = OtRoom::active()->orderBy('name')->get();

        $pendingPreOp = OtSurgerySchedule::with(['surgeryRequest.patient', 'preOpChecklist'])
            ->whereIn('status', [
                OtSurgerySchedule::STATUS_SCHEDULED,
                OtSurgerySchedule::STATUS_PRE_OP_PENDING,
            ])
            ->whereDate('scheduled_start', $today)
            ->orderByDesc('emergency_fast_track')
            ->orderBy('scheduled_start')
            ->get()
            ->filter(fn ($s) => ! $s->preOpChecklist || ! $s->preOpChecklist->isReady());

        $waitingTransfer = OtTransfer::with('schedule.surgeryRequest.patient')
            ->where('status', 'Initiated')
            ->whereNull('arrived_at')
            ->get();

        $pacuPatients = OtPacuRecord::with('schedule.surgeryRequest.patient')
            ->whereNull('discharged_at')
            ->orderByDesc('admitted_at')
            ->get();

        $stats = [
            'today_total' => $todaySchedules->count(),
            'running' => $runningSurgeries->count(),
            'delayed' => $delayedCases->count(),
            'emergency' => $emergencyCases->count(),
            'pending_pre_op' => $pendingPreOp->count(),
            'waiting_transfer' => $waitingTransfer->count(),
            'in_pacu' => $pacuPatients->count(),
            'pending_requests' => OtSurgeryRequest::whereIn('status', [
                OtSurgeryRequest::STATUS_SUBMITTED,
                OtSurgeryRequest::STATUS_UNDER_REVIEW,
            ])->count(),
        ];

        $notifications = OtNotification::unread()->latest()->take(10)->get();

        // Pending requests list (for new "Pending Requests" panel)
        $pendingRequests = OtSurgeryRequest::with(['patient','surgeryType','primarySurgeon'])
            ->whereIn('status', [
                OtSurgeryRequest::STATUS_SUBMITTED,
                OtSurgeryRequest::STATUS_UNDER_REVIEW,
                OtSurgeryRequest::STATUS_PENDING_INFORMATION,
                OtSurgeryRequest::STATUS_SENT_BACK,
            ])
            ->latest()
            ->take(10)
            ->get();

        // Recent activity feed — last 10 audit events across OT
        $recentActivity = \App\Models\Ot\OtAuditLog::with('user')
            ->latest()
            ->take(10)
            ->get();

        // This-week stats footer
        $weekStart = \Carbon\Carbon::now()->startOfWeek();
        $monthStart = \Carbon\Carbon::now()->startOfMonth();
        $weekStats = [
            'surgeries_done_week'   => OtSurgerySchedule::where('actual_end', '>=', $weekStart)
                ->whereIn('status', [
                    OtSurgerySchedule::STATUS_SURGERY_COMPLETED,
                    OtSurgerySchedule::STATUS_IN_RECOVERY,
                    OtSurgerySchedule::STATUS_TRANSFERRED_BACK,
                    OtSurgerySchedule::STATUS_CLOSED,
                ])->count(),
            'surgeries_done_month'  => OtSurgerySchedule::where('actual_end', '>=', $monthStart)
                ->whereIn('status', [
                    OtSurgerySchedule::STATUS_SURGERY_COMPLETED,
                    OtSurgerySchedule::STATUS_IN_RECOVERY,
                    OtSurgerySchedule::STATUS_TRANSFERRED_BACK,
                    OtSurgerySchedule::STATUS_CLOSED,
                ])->count(),
            'cancelled_week'        => OtSurgerySchedule::where('updated_at', '>=', $weekStart)
                ->where('status', OtSurgerySchedule::STATUS_CANCELLED)->count(),
            'emergency_week'        => OtSurgerySchedule::where('created_at', '>=', $weekStart)
                ->where('emergency_fast_track', true)->count(),
        ];

        return view('ot.dashboard.index', [
            'stats' => $stats,
            'todaySchedules' => $todaySchedules,
            'runningSurgeries' => $runningSurgeries,
            'delayedCases' => $delayedCases,
            'emergencyCases' => $emergencyCases,
            'rooms' => $rooms,
            'pendingPreOp' => $pendingPreOp,
            'waitingTransfer' => $waitingTransfer,
            'pacuPatients' => $pacuPatients,
            'notifications' => $notifications,
            'pendingRequests' => $pendingRequests,
            'recentActivity' => $recentActivity,
            'weekStats' => $weekStats,
            'dash' => $this->dash,
            'roomStates' => $this->roomStates,
        ]);
    }

    /**
     * Mark a notification as read (called via small POST from dashboard).
     */
    public function markNotificationRead(Request $request, $id)
    {
        $this->gate('ot_dashboard_access');
        $n = OtNotification::find($id);
        if ($n && ! $n->read_at) {
            $n->update(['read_at' => now()]);
        }
        return back();
    }

    /** Mark all unread notifications as read. */
    public function markAllNotificationsRead()
    {
        $this->gate('ot_dashboard_access');
        OtNotification::whereNull('read_at')->update(['read_at' => now()]);
        return back()->with('success', 'All notifications marked as read.');
    }
}
