<?php
namespace App\Http\Controllers\Billing;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class BillingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $transactions = Transaction::with([
                'patient',
                'opdPatient.doctor',
                'ipdPatient.doctor',
            ])
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->get();

        $today = Carbon::today();

        $totalBillingSum   = (float) $transactions->sum('net_amount');
        $totalBillingCount = $transactions->count();

        $todayTx = $transactions->filter(function ($t) use ($today) {
            return $t->payment_date && Carbon::parse($t->payment_date)->isSameDay($today);
        });

        $todayOpdTx = $todayTx->filter(fn ($t) => strcasecmp((string) $t->section, 'opd') === 0);
        $todayIpdTx = $todayTx->filter(fn ($t) => strcasecmp((string) $t->section, 'ipd') === 0);

        $pendingCount = $transactions->where('status', 'pending')->count();
        $revenueSum   = (float) $transactions->whereIn('status', ['successed', 'paid'])->sum('net_amount');

        $stats = [
            'total_billing_sum'     => $totalBillingSum,
            'total_billing_count'   => $totalBillingCount,
            'today_billing_sum'     => (float) $todayTx->sum('net_amount'),
            'today_billing_count'   => $todayTx->count(),
            'today_opd_sum'         => (float) $todayOpdTx->sum('net_amount'),
            'today_opd_count'       => $todayOpdTx->count(),
            'today_ipd_sum'         => (float) $todayIpdTx->sum('net_amount'),
            'today_ipd_count'       => $todayIpdTx->count(),
            'pending_count'         => $pendingCount,
            'revenue_sum'           => $revenueSum,
        ];

        return view('billing.index', compact('transactions', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
