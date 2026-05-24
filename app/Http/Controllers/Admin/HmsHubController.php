<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/**
 * Reports Hub · Audit Log Viewer · Doctor Wallet UI
 * Three additions that complete the project's compliance + visibility surface.
 */
class HmsHubController extends Controller
{
    public function __construct() { $this->middleware('auth'); }

    /** Reports Hub — links to every report screen in the project. */
    public function reports()
    {
        $sections = [
            'Patient Care' => [
                ['Package Utilization',  'packages.reports.utilization',  'graph-up',          'Allowed vs consumed per service'],
                ['Department Packages',  'packages.reports.department',   'diagram-3',         'Enrolment by department'],
                ['Package Revenue',      'packages.reports.revenue',      'cash-coin',         'Revenue per package'],
                ['Package Expiry',       'packages.reports.expiry',       'exclamation-triangle','Enrolments expiring in 7 days'],
            ],
            'Billing & Finance' => [
                ['All Bills',            'billing.bills.index',           'receipt',           'Master bills register'],
                ['IPD Billing',          'billing.ipd-billing.index',     'hospital',          'IPD-specific transactions'],
                ['OPD Billing',          'billing.opd-billing.index',     'stethoscope',       'OPD-specific transactions'],
                ['Emergency Billing',    null,                            'heart',             'ER-specific bills (filtered)'],
                ['Pathology Billing',    null,                            'eyedropper',        'Lab bills (filtered)'],
                ['Radiology Billing',    null,                            'broadcast',         'Imaging bills (filtered)'],
                ['GL Journal',           'accounting.journal.index',      'journal-text',      'Auto-posted GL entries'],
                ['Chart of Accounts',    'accounting.coa.index',          'diagram-3',         '78 accounts with balances'],
            ],
            'Clinical' => [
                ['ICU Mortality',        'icu.mortality.index',           'heartbreak',        'ICU mortality breakdown'],
                ['ICU Infection',        'icu.infection.reports',         'shield-exclamation','Infection cluster detection'],
                ['NICU Dashboard',       'nicu.dashboard',                'emoji-smile',       'Live NICU KPIs'],
                ['ER Dashboard',         'er.dashboard',                  'heart',             'ER live operations'],
            ],
            'Insurance & Claims' => [
                ['Insurance Claims',     'insurance.claims.index',        'shield-check',      'All claims with status'],
                ['Insurance Policies',   'insurance.policies.index',      'shield',            'Active policies'],
                ['Insurance Payers',     'insurance.payers.index',        'building',          'Configured insurers'],
            ],
            'HR & People' => [
                ['Employees',            'hr.employees.index',            'people',            'Master HR register'],
                ['Payroll',              'hr.payroll.index',              'cash-stack',        'Monthly payroll'],
                ['Attendance',           'hr.attendance.index',           'calendar-check',    'Attendance log'],
                ['Doctor Wallet',        'admin.hub.wallet',               'wallet',            'Doctor commission tracking'],
            ],
            'Inventory & Stock' => [
                ['Stock Ledger',         'inventory.stock-ledger',        'journal-medical',   'Immutable stock_movements'],
                ['Items',                'inventory.items.index',         'box',               'Inventory item master'],
                ['Warehouses',           'inventory.warehouses.index',    'building',          'Warehouse master'],
            ],
            'System & Audit' => [
                ['Audit Log',            'admin.hub.audit',               'eye',               '1260+ activity records'],
                ['Roles & Permissions',  'roles.index',                   'shield-check',      'RBAC matrix'],
                ['Settings',             'admin.settings.index',          'gear',              'Application settings'],
            ],
        ];

        return view('admin.hub.reports', compact('sections'));
    }

    /** Audit Log Viewer — Spatie activitylog browser. */
    public function audit(Request $request)
    {
        $q = DB::table('activity_log')
            ->leftJoin('users', 'users.id', '=', 'activity_log.causer_id')
            ->select('activity_log.*', 'users.name as causer_name');

        if ($request->filled('log_name')) {
            $q->where('log_name', $request->log_name);
        }
        if ($request->filled('event')) {
            $q->where('event', $request->event);
        }
        if ($request->filled('subject_type')) {
            $q->where('subject_type', 'like', '%' . $request->subject_type . '%');
        }
        if ($request->filled('causer')) {
            $q->where('users.name', 'like', '%' . $request->causer . '%');
        }
        if ($request->filled('date_from')) {
            $q->whereDate('activity_log.created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $q->whereDate('activity_log.created_at', '<=', $request->date_to);
        }

        $logs = $q->orderByDesc('activity_log.id')->paginate(50)->withQueryString();

        $stats = [
            'total'       => DB::table('activity_log')->count(),
            'today'       => DB::table('activity_log')->whereDate('created_at', today())->count(),
            'this_week'   => DB::table('activity_log')->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'distinct_users' => DB::table('activity_log')->distinct('causer_id')->count('causer_id'),
        ];
        $events = DB::table('activity_log')->select('event', DB::raw('COUNT(*) as n'))
            ->groupBy('event')->orderByDesc('n')->limit(8)->get();
        $logNames = DB::table('activity_log')->select('log_name', DB::raw('COUNT(*) as n'))
            ->groupBy('log_name')->orderByDesc('n')->get();

        return view('admin.hub.audit', compact('logs', 'stats', 'events', 'logNames'));
    }

    /** Doctor Wallet — commission tracking. */
    public function wallet(Request $request)
    {
        $txns = DB::table('doctor_wallet_transactions as wt')
            ->leftJoin('employees as e', 'e.id', '=', 'wt.employee_id')
            ->select('wt.*',
                DB::raw("CONCAT(IFNULL(e.first_name, ''), ' ', IFNULL(e.last_name, '')) as employee_name"),
                'e.employee_code')
            ->orderByDesc('wt.id')->paginate(50);

        $byEmployee = DB::table('doctor_wallet_transactions as wt')
            ->leftJoin('employees as e', 'e.id', '=', 'wt.employee_id')
            ->select(
                DB::raw("CONCAT(IFNULL(e.first_name, ''), ' ', IFNULL(e.last_name, '')) as name"),
                'e.employee_code',
                DB::raw('COUNT(wt.id) as txns'),
                DB::raw('SUM(wt.gross_amount) as gross'),
                DB::raw('SUM(wt.commission_amount) as commission')
            )
            ->groupBy('e.id', 'e.first_name', 'e.last_name', 'e.employee_code')
            ->orderByDesc('commission')->get();

        $stats = [
            'total_txns'      => DB::table('doctor_wallet_transactions')->count(),
            'gross_revenue'   => (float) DB::table('doctor_wallet_transactions')->sum('gross_amount'),
            'commission_due'  => (float) DB::table('doctor_wallet_transactions')->where('status', 'accrued')->sum('commission_amount'),
            'commission_paid' => (float) DB::table('doctor_wallet_transactions')->where('status', 'paid')->sum('commission_amount'),
        ];

        return view('admin.hub.wallet', compact('txns', 'byEmployee', 'stats'));
    }
}
