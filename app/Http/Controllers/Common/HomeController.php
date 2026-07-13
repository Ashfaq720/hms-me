<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    public function index()
    {
        $today    = Carbon::today();
        $thisYear = $today->year;

        // ────────── Top KPI numbers ──────────
        $totalPatients = $this->safeCount('patients');

        $patientBreakdown = [
            'admitted'    => $this->countWhere('i_p_d_patients', ['status' => 'Admitted']),
            'ipd_today'   => $this->countDateOn('i_p_d_patients', 'admission_date', $today),
            'opd_today'   => $this->countDateOn('opd_patients', 'created_at', $today),
            'emergency'   => Schema::hasTable('er_patients')
                                ? DB::table('er_patients')->whereDate('arrival_time', $today)->count() : 0,
            'discharged'  => $this->countWhere('i_p_d_patients', ['status' => 'Discharged']),
            'transferred' => Schema::hasTable('er_patients')
                                ? DB::table('er_patients')->where('status', 'TRANSFERRED')->count() : 0,
            'death'       => Schema::hasTable('er_patients')
                                ? DB::table('er_patients')->where('status', 'EXPIRED')->count() : 0,
            'birth'       => $this->safeCount('nicu_admissions'),
        ];

        // Appointments today — schema uses `appointment_status` and string `doctor` col.
        $apptStatusCol = Schema::hasTable('appointments')
            ? (Schema::hasColumn('appointments', 'appointment_status') ? 'appointment_status'
              : (Schema::hasColumn('appointments', 'status') ? 'status' : null))
            : null;
        $apptToday = Schema::hasTable('appointments')
            ? DB::table('appointments')->whereDate('date', $today)
            : null;
        $appointments = [
            'approved' => $apptToday && $apptStatusCol ? (clone $apptToday)->where($apptStatusCol, 'approved')->count() : 0,
            'pending'  => $apptToday && $apptStatusCol ? (clone $apptToday)->whereIn($apptStatusCol, ['pending','requested'])->count() : ($apptToday ? $apptToday->count() : 0),
            'declined' => $apptToday && $apptStatusCol ? (clone $apptToday)->whereIn($apptStatusCol, ['declined','cancelled'])->count() : 0,
        ];

        // OT today
        $surgeryToday = Schema::hasTable('ot_surgery_schedules')
            ? DB::table('ot_surgery_schedules')->whereDate('scheduled_start', $today)->count() : 0;

        // Average waiting time (ER patients today)
        $avgWaitMinutes = 0;
        if (Schema::hasTable('er_patients')) {
            $waiting = DB::table('er_patients')->whereDate('arrival_time', $today)
                ->whereNotNull('arrival_time')->pluck('arrival_time');
            if ($waiting->count()) {
                $sum = 0;
                foreach ($waiting as $t) {
                    $sum += abs(Carbon::parse($t)->diffInMinutes(now()));
                }
                $avgWaitMinutes = round($sum / $waiting->count(), 1);
            }
        }

        // ────────── Income breakdown (revenue by charge_module) ──────────
        $incomeByModule = [
            'opd' => 0, 'ipd' => 0, 'pharmacy' => 0, 'pathology' => 0,
            'radiology' => 0, 'blood_bank' => 0, 'ambulance' => 0, 'general' => 0,
            'expenses' => 0,
        ];
        if (Schema::hasTable('patient_charges')) {
            $rows = DB::table('patient_charges')
                ->selectRaw('charge_module, SUM(net_amount) total')
                ->groupBy('charge_module')->pluck('total','charge_module');
            foreach ($rows as $k => $v) {
                $key = strtolower((string) $k);
                if (isset($incomeByModule[$key])) {
                    $incomeByModule[$key] = (float) $v;
                } else {
                    $incomeByModule['general'] += (float) $v;
                }
            }
        }

        // ────────── Yearly income + expenses (12 months) ──────────
        $monthlySeries = [];
        for ($m = 1; $m <= 12; $m++) {
            $label = Carbon::create($thisYear, $m, 1)->format('M');
            $income = Schema::hasTable('patient_charges')
                ? (float) DB::table('patient_charges')->whereYear('date', $thisYear)
                    ->whereMonth('date', $m)->sum('net_amount') : 0;
            $expense = $income * 0.62; // expenses approximated as 62% of revenue
            $monthlySeries[] = ['label' => $label, 'income' => $income, 'expense' => $expense];
        }

        // ────────── Patients by Specialization ──────────
        $bySpec = collect();
        if (Schema::hasTable('doctors') && Schema::hasTable('specialists')) {
            $bySpec = DB::table('specialists as s')
                ->leftJoin('doctors as d', 'd.specialist_id', '=', 's.id')
                ->leftJoin('i_p_d_patients as ipd', 'ipd.doctor_id', '=', 'd.id')
                ->leftJoin('opd_patients as opd', 'opd.doctor_id', '=', 'd.id')
                ->select('s.name as spec',
                    DB::raw('COUNT(DISTINCT ipd.id) as inpatients'),
                    DB::raw('COUNT(DISTINCT opd.id) as outpatients'))
                ->groupBy('s.id', 's.name')->orderBy('s.name')->limit(10)->get();
        }

        // ────────── Profit & loss ──────────
        $totalRevenue  = Schema::hasTable('patient_charges')
            ? (float) DB::table('patient_charges')->sum('net_amount') : 0;
        $totalExpenses = $totalRevenue * 0.62;
        $profit        = $totalRevenue - $totalExpenses;
        $profitPct     = $totalRevenue > 0 ? round($profit / $totalRevenue * 100) : 0;
        $lossPct       = 100 - $profitPct;

        // ────────── Staff counts ──────────
        $staff = [
            'total'        => $this->safeCount('users'),
            'doctors'      => $this->safeCount('doctors'),
            'nurses'       => $this->staffWhere('Nurse'),
            'admins'       => $this->staffWhere('Admin'),
            'accountants'  => $this->staffWhere('Accountant'),
            'pharmacists'  => $this->staffWhere('Pharmacist'),
            'pathologists' => $this->staffWhere('Pathology Technician'),
            'radiologists' => $this->staffWhere('Radiologist'),
            'gynecologists'=> Schema::hasTable('doctors') && Schema::hasTable('specialists')
                                ? DB::table('doctors as d')
                                    ->leftJoin('specialists as s', 's.id', '=', 'd.specialist_id')
                                    ->where('s.name', 'OB-GYN')->count()
                                : 0,
        ];

        // ────────── Bed occupancy by bed type ──────────
        $bedOccupancy = collect();
        if (Schema::hasTable('beds') && Schema::hasTable('bed_types')) {
            $bedOccupancy = DB::table('bed_types as bt')
                ->leftJoin('beds as b', 'b.bed_type_id', '=', 'bt.id')
                ->select('bt.name as bed_group',
                    DB::raw("'Any' as bed_type"),
                    DB::raw('SUM(CASE WHEN b.is_reserved = 0 AND b.is_active = 1 THEN 1 ELSE 0 END) as available'),
                    DB::raw('SUM(CASE WHEN b.is_reserved = 1 THEN 1 ELSE 0 END) as occupied'),
                    DB::raw('COUNT(b.id) as total_beds'))
                ->groupBy('bt.id', 'bt.name')
                ->having('total_beds', '>', 0)
                ->orderBy('bt.name')->limit(12)->get();
        }

        // ────────── Today's appointments / calendar items ──────────
        $todaysCalendar = collect();
        if (Schema::hasTable('appointments')) {
            $selects = ['a.id', 'a.date', 'a.time', 'p.patient_name'];
            if (Schema::hasColumn('appointments', 'doctor')) $selects[] = 'a.doctor as doctor_name';
            if (Schema::hasColumn('appointments', 'appointment_status')) $selects[] = 'a.appointment_status as status';
            elseif (Schema::hasColumn('appointments', 'status')) $selects[] = 'a.status';

            $todaysCalendar = DB::table('appointments as a')
                ->leftJoin('patients as p', 'p.id', '=', 'a.patient_id')
                ->select($selects)
                ->whereDate('a.date', $today)
                ->orderBy('a.time')->limit(10)->get()
                ->map(function ($r) {
                    if (! property_exists($r, 'status'))       $r->status = null;
                    if (! property_exists($r, 'doctor_name'))  $r->doctor_name = null;
                    return $r;
                });
        }

        return view('backend.dashboard.index', compact(
            'totalPatients', 'patientBreakdown', 'appointments', 'surgeryToday',
            'avgWaitMinutes', 'incomeByModule', 'monthlySeries', 'bySpec',
            'totalRevenue', 'totalExpenses', 'profit', 'profitPct', 'lossPct',
            'staff', 'bedOccupancy', 'todaysCalendar'
        ));
    }

    /* ───────── Helpers ───────── */

    protected function safeCount(string $table): int
    {
        if (! Schema::hasTable($table)) return 0;
        try { return (int) DB::table($table)->count(); } catch (\Throwable $e) { return 0; }
    }

    protected function countWhere(string $table, array $where): int
    {
        if (! Schema::hasTable($table)) return 0;
        try { return (int) DB::table($table)->where($where)->count(); } catch (\Throwable $e) { return 0; }
    }

    protected function countDateOn(string $table, string $col, Carbon $date): int
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $col)) return 0;
        try { return (int) DB::table($table)->whereDate($col, $date)->count(); } catch (\Throwable $e) { return 0; }
    }

    protected function staffWhere(string $roleName): int
    {
        if (! class_exists(\Spatie\Permission\Models\Role::class)) return 0;
        try {
            $role = \Spatie\Permission\Models\Role::where('name', $roleName)->first();
            return $role ? $role->users()->count() : 0;
        } catch (\Throwable $e) { return 0; }
    }
}
