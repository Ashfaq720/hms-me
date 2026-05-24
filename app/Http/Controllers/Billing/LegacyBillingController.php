<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Billing\Bill;
use Illuminate\Http\Request;

/**
 * Single controller for the 7 legacy billing menu items
 * (Emergency, Pathology, Radiology, Utility, Blood Bank, etc.)
 * Each maps to a bill_type filter on the unified bills table.
 */
class LegacyBillingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private array $categories = [
        'emergency'  => ['label' => 'Emergency Billing',  'types' => ['er'],          'icon' => 'heart-pulse',  'colour' => 'danger'],
        'pathology'  => ['label' => 'Pathology Billing',  'types' => ['lab'],         'icon' => 'eyedropper',   'colour' => 'info'],
        'radiology'  => ['label' => 'Radiology Billing',  'types' => ['radiology'],   'icon' => 'broadcast',    'colour' => 'primary'],
        'utility'    => ['label' => 'Utility Billing',    'types' => ['ambulance'],   'icon' => 'lightning',    'colour' => 'warning'],
        'blood-bank' => ['label' => 'Blood Bank Billing', 'types' => ['blood_bank', 'other'], 'icon' => 'droplet', 'colour' => 'danger'],
        'ot'         => ['label' => 'OT Billing',         'types' => ['ot', 'surgery'], 'icon' => 'scissors',   'colour' => 'danger'],
    ];

    public function index(Request $request, string $category)
    {
        if (! isset($this->categories[$category])) abort(404);
        $meta = $this->categories[$category];

        $bills = Bill::with(['patient', 'encounter', 'payments'])
            ->whereIn('bill_type', $meta['types'])
            ->when($request->string('q')->toString(), function ($q, $term) {
                $q->where(function ($w) use ($term) {
                    $w->where('bill_no', 'like', "%$term%")
                        ->orWhereHas('patient', fn ($p) => $p->where('patient_name', 'like', "%$term%"));
                });
            })
            ->when($request->string('status')->toString(), fn ($q, $s) => $q->where('status', $s))
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $totals = [
            'grand'   => (float) Bill::whereIn('bill_type', $meta['types'])->sum('grand_total'),
            'paid'    => (float) Bill::whereIn('bill_type', $meta['types'])->sum('paid_total'),
            'due'     => (float) Bill::whereIn('bill_type', $meta['types'])->sum('balance_due'),
            'count'   => Bill::whereIn('bill_type', $meta['types'])->count(),
        ];

        return view('billing.legacy.index', compact('bills', 'meta', 'category', 'totals'));
    }

    public function pdf(Request $request, Bill $bill)
    {
        // Encounter has no direct department relation — pull dept via doctor.
        $bill->load(['patient', 'encounter.doctor.department', 'items', 'payments']);
        return view('billing.legacy.pdf', compact('bill'));
    }
}
