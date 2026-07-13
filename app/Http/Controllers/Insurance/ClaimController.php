<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use App\Models\Billing\Bill;
use App\Models\Insurance\Claim;
use App\Services\Insurance\ClaimBuilderService;
use Illuminate\Http\Request;

class ClaimController extends Controller
{
    public function __construct(private ClaimBuilderService $builder)
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $this->gate('insurance.claim.view');
        $claims = Claim::with(['patient', 'payer', 'policy'])
            ->when($request->string('status')->toString(), fn ($q, $s) => $q->where('status', $s))
            ->when($request->string('q')->toString(), function ($q, $term) {
                $q->where(function ($w) use ($term) {
                    $w->where('claim_no', 'like', "%$term%")
                        ->orWhere('bill_reference', 'like', "%$term%")
                        ->orWhereHas('patient', fn ($p) => $p->where('patient_name', 'like', "%$term%"));
                });
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();
        return view('insurance.claims.index', compact('claims'));
    }

    public function show(Claim $claim)
    {
        $this->gate('insurance.claim.view');
        $claim->load(['items', 'patient', 'payer', 'policy', 'encounter']);
        return view('insurance.claims.show', compact('claim'));
    }

    public function buildFromBill(Bill $bill)
    {
        $this->gate('insurance.claim.submit');
        try {
            $claim = $this->builder->buildFromBill($bill);
        } catch (\Throwable $e) {
            return back()->withErrors(['claim' => $e->getMessage()]);
        }
        return redirect()->route('insurance.claims.show', $claim)
            ->with('success', 'Claim built from bill ' . $bill->bill_no);
    }

    public function submit(Claim $claim)
    {
        $this->gate('insurance.claim.submit');
        try {
            $this->builder->submit($claim);
        } catch (\Throwable $e) {
            return back()->withErrors(['claim' => $e->getMessage()]);
        }
        return back()->with('success', 'Claim submitted to payer.');
    }

    public function approve(Request $request, Claim $claim)
    {
        $this->gate('insurance.claim.adjudicate');
        $data = $request->validate([
            'approved_amount' => ['required', 'numeric', 'min:0'],
        ]);
        try {
            $this->builder->approve($claim, (float) $data['approved_amount']);
        } catch (\Throwable $e) {
            return back()->withErrors(['claim' => $e->getMessage()]);
        }
        return back()->with('success', 'Claim approved.');
    }

    public function settle(Request $request, Claim $claim)
    {
        $this->gate('insurance.claim.settle');
        $data = $request->validate([
            'settled_amount' => ['required', 'numeric', 'min:0'],
        ]);
        try {
            $this->builder->settle($claim, (float) $data['settled_amount']);
        } catch (\Throwable $e) {
            return back()->withErrors(['claim' => $e->getMessage()]);
        }
        return back()->with('success', 'Claim settled.');
    }

    public function reject(Request $request, Claim $claim)
    {
        $this->gate('insurance.claim.adjudicate');
        $data = $request->validate(['reason' => ['required', 'string', 'max:500']]);
        $this->builder->reject($claim, $data['reason']);
        return back()->with('success', 'Claim rejected.');
    }

    private function gate(string $permission): void
    {
        if (! auth()->user()?->can($permission)) {
            abort(403, "Missing permission: {$permission}");
        }
    }
}
