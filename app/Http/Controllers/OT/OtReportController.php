<?php

namespace App\Http\Controllers\OT;

use App\Http\Controllers\Controller;
use App\Models\Ot\OtAuditLog;
use App\Models\Ot\OtConsumableUsage;
use App\Models\Ot\OtSurgerySchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OtReportController extends OtBaseController
{
    public function index()
    {
        $this->gate('ot_reports_access');
        return view('ot.reports.index');
    }

    public function surgeryReport(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $schedules = OtSurgerySchedule::with(['surgeryRequest.patient', 'surgeryRequest.surgeryType', 'room'])
            ->whereBetween('scheduled_start', [$from, $to])
            ->orderBy('scheduled_start')
            ->get();

        return view('ot.reports.surgeries', compact('schedules', 'from', 'to'));
    }

    public function utilization(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $utilization = OtSurgerySchedule::selectRaw('ot_room_id, COUNT(*) as case_count, SUM(TIMESTAMPDIFF(MINUTE, scheduled_start, scheduled_end)) as scheduled_minutes')
            ->whereBetween('scheduled_start', [$from, $to])
            ->whereNotIn('status', [OtSurgerySchedule::STATUS_CANCELLED])
            ->with('room')
            ->groupBy('ot_room_id')
            ->get();

        return view('ot.reports.utilization', compact('utilization', 'from', 'to'));
    }

    public function cancellations(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $cancellations = OtSurgerySchedule::with(['surgeryRequest.patient', 'room'])
            ->whereBetween('scheduled_start', [$from, $to])
            ->where('status', OtSurgerySchedule::STATUS_CANCELLED)
            ->get();

        return view('ot.reports.cancellations', compact('cancellations', 'from', 'to'));
    }

    public function consumables(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $usages = OtConsumableUsage::with(['schedule.surgeryRequest.patient', 'consumable'])
            ->whereBetween('created_at', [$from, $to])
            ->get();

        return view('ot.reports.consumables', compact('usages', 'from', 'to'));
    }

    public function revenue(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $usages = OtConsumableUsage::whereBetween('created_at', [$from, $to])->where('is_billed', true)->sum('amount');
        $surgeryCount = OtSurgerySchedule::whereBetween('scheduled_start', [$from, $to])
            ->whereNotIn('status', [OtSurgerySchedule::STATUS_CANCELLED])->count();

        return view('ot.reports.revenue', compact('usages', 'surgeryCount', 'from', 'to'));
    }

    public function auditTrail(Request $request)
    {
        $logs = OtAuditLog::query();

        if ($entity = $request->get('entity_type')) {
            $logs->where('entity_type', $entity);
        }
        if ($action = $request->get('action')) {
            $logs->where('action', $action);
        }
        if ($from = $request->get('from')) {
            $logs->where('created_at', '>=', Carbon::parse($from)->startOfDay());
        }
        if ($to = $request->get('to')) {
            $logs->where('created_at', '<=', Carbon::parse($to)->endOfDay());
        }

        $logs = $logs->latest()->paginate(50)->withQueryString();

        return view('ot.reports.audit', compact('logs'));
    }

    protected function dateRange(Request $request): array
    {
        $from = $request->get('from')
            ? Carbon::parse($request->get('from'))->startOfDay()
            : Carbon::now()->startOfMonth();
        $to = $request->get('to')
            ? Carbon::parse($request->get('to'))->endOfDay()
            : Carbon::now()->endOfMonth();

        return [$from, $to];
    }
}
