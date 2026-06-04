<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Billing\Bill;
use App\Models\Encounter\Encounter;
use App\Services\Billing\BillingService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BillController extends Controller
{
    public function __construct(private BillingService $billing)
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $this->gate('billing.bill.view');

        $bills = Bill::with(['patient', 'encounter'])
            ->when($request->string('q')->toString(), function ($q, $term) {
                $q->where(function ($w) use ($term) {
                    $w->where('bill_no', 'like', "%$term%")
                        ->orWhereHas('patient', fn ($p) => $p->where('patient_name', 'like', "%$term%"));
                });
            })
            ->when($request->string('status')->toString(), fn ($q, $s) => $q->where('status', $s))
            ->when($request->string('type')->toString(), fn ($q, $t) => $q->where('bill_type', $t))
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('billing.bills.index', compact('bills'));
    }

    public function show(Bill $bill)
    {
        $this->gate('billing.bill.view');
        $bill->load(['patient', 'encounter', 'items.service', 'payments', 'refunds', 'discounts']);
        return view('billing.bills.show', compact('bill'));
    }

    public function assembleFromEncounter(Request $request, Encounter $encounter)
    {
        $this->gate('billing.bill.manage');
        $bill = $this->billing->assembleFromEncounter($encounter);
        return redirect()->route('billing.bills.show', $bill)
            ->with('success', 'Bill assembled from encounter postings.');
    }

    public function finalize(Bill $bill)
    {
        $this->gate('billing.bill.finalize');
        try {
            $this->billing->finalize($bill);
        } catch (\Throwable $e) {
            return back()->withErrors(['bill' => $e->getMessage()]);
        }
        return back()->with('success', 'Bill finalized.');
    }

    public function collectPayment(Request $request, Bill $bill)
    {
        $this->gate('billing.payment.collect');

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', Rule::in(['cash', 'card', 'mfs', 'cheque', 'bank_transfer', 'insurance', 'corporate', 'advance', 'other'])],
            'reference_no' => ['nullable', 'string', 'max:64'],
            'payment_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->billing->collectPayment($bill, $data);
        } catch (\Throwable $e) {
            return back()->withErrors(['payment' => $e->getMessage()]);
        }
        return back()->with('success', 'Payment collected.');
    }

    public function applyDiscount(Request $request, Bill $bill)
    {
        $this->gate('billing.discount.approve');
        $data = $request->validate([
            'kind' => ['required', Rule::in(['discount', 'waiver'])],
            'mode' => ['required', Rule::in(['percent', 'flat'])],
            'value' => ['required', 'numeric', 'min:0'],
            'reason' => ['required', 'string', 'max:191'],
        ]);
        $this->billing->applyDiscount($bill, $data);
        return back()->with('success', ucfirst($data['kind']) . ' applied.');
    }

    public function cancel(Request $request, Bill $bill)
    {
        $this->gate('billing.bill.cancel');
        $data = $request->validate(['reason' => ['required', 'string', 'max:255']]);
        try {
            $this->billing->cancel($bill, $data['reason']);
        } catch (\Throwable $e) {
            return back()->withErrors(['bill' => $e->getMessage()]);
        }
        return back()->with('success', 'Bill cancelled.');
    }

    public function print(Bill $bill)
    {
        $this->gate('billing.bill.view');
        $bill->load(['patient', 'encounter', 'items', 'payments', 'discounts']);
        return view('billing.bills.print', compact('bill'));
    }

    private function gate(string $permission): void
    {
        if (! auth()->user()?->can($permission)) {
            abort(403, "Missing permission: {$permission}");
        }
    }
}
