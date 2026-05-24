<?php

namespace App\Http\Controllers\Insight;

use App\Http\Controllers\Controller;
use App\Models\Billing\Bill;
use App\Models\Encounter\Encounter;
use App\Models\Insurance\Claim;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryItemBatch;
use App\Models\Inventory\StockMovement;
use App\Models\Patient;
use App\Models\ServiceCharge\ServiceChargePosting;
use Illuminate\Support\Carbon;

class InsightDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->gate('dashboard.executive.view');

        $today = Carbon::today();
        $monthStart = $today->copy()->startOfMonth();
        $expiryWindow = $today->copy()->addDays(30);

        $kpi = [
            'patients_total' => Patient::count(),
            'encounters_open' => Encounter::open()->count(),
            'encounters_today' => Encounter::whereDate('started_at', $today)->count(),
            'opd_today' => Encounter::where('encounter_type', 'OPD')->whereDate('started_at', $today)->count(),
            'ipd_open' => Encounter::where('encounter_type', 'IPD')->open()->count(),
            'er_today' => Encounter::where('encounter_type', 'ER')->whereDate('started_at', $today)->count(),
            'bills_open' => Bill::open()->count(),
            'revenue_today' => (float) Bill::whereDate('bill_date', $today)->sum('grand_total'),
            'revenue_month' => (float) Bill::whereBetween('bill_date', [$monthStart, $today])->sum('grand_total'),
            'collection_today' => (float) Bill::whereDate('bill_date', $today)->sum('paid_total'),
            'outstanding_due' => (float) Bill::open()->sum('balance_due'),
            'service_charge_postings' => ServiceChargePosting::posted()->count(),
            'service_charge_revenue' => (float) ServiceChargePosting::posted()->sum('net_amount'),
            'stock_items' => InventoryItem::where('is_active', true)->count(),
            'stock_movements_today' => StockMovement::whereDate('performed_at', $today)->count(),
            'near_expiry_batches' => InventoryItemBatch::whereNotNull('expiry_date')
                ->whereBetween('expiry_date', [$today, $expiryWindow])
                ->where('current_qty', '>', 0)
                ->count(),
            'expired_batches' => InventoryItemBatch::whereNotNull('expiry_date')
                ->where('expiry_date', '<', $today)
                ->where('current_qty', '>', 0)
                ->count(),
            'claims_open' => Claim::whereIn('status', ['draft', 'submitted', 'under_review'])->count(),
        ];

        $revenueByType = Bill::query()
            ->whereBetween('bill_date', [$monthStart, $today])
            ->selectRaw('bill_type, SUM(grand_total) as total, COUNT(*) as cnt')
            ->groupBy('bill_type')
            ->orderByDesc('total')
            ->get();

        $recentBills = Bill::with('patient')->latest('id')->limit(8)->get();
        $criticalBatches = InventoryItemBatch::with('item')
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [$today, $expiryWindow])
            ->where('current_qty', '>', 0)
            ->orderBy('expiry_date')
            ->limit(10)
            ->get();

        return view('insight.dashboard', compact('kpi', 'revenueByType', 'recentBills', 'criticalBatches'));
    }

    private function gate(string $permission): void
    {
        if (! auth()->user()?->can($permission)) {
            abort(403, "Missing permission: {$permission}");
        }
    }
}
