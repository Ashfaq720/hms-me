<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\ChartOfAccount;
use Illuminate\Http\Request;

class ChartOfAccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->gate('accounting.coa.view');

        // Per-account debit/credit totals pulled from gl_postings
        $balances = \Illuminate\Support\Facades\DB::table('gl_postings')
            ->select('chart_of_account_id',
                \Illuminate\Support\Facades\DB::raw('SUM(debit) as debit_total'),
                \Illuminate\Support\Facades\DB::raw('SUM(credit) as credit_total'),
                \Illuminate\Support\Facades\DB::raw('COUNT(*) as posting_count'))
            ->groupBy('chart_of_account_id')->get()->keyBy('chart_of_account_id');

        $accounts = ChartOfAccount::with('parent')->orderBy('code')->get();
        $grouped = $accounts->groupBy('account_type');

        $kpi = [
            'total' => $accounts->count(),
            'active' => $accounts->where('is_active', true)->count(),
            'postable' => $accounts->where('is_postable', true)->count(),
            'with_postings' => $balances->count(),
            'by_type' => $accounts->groupBy('account_type')->map->count(),
        ];

        return view('accounting.coa.index', compact('accounts', 'grouped', 'balances', 'kpi'));
    }

    public function create()
    {
        $this->gate('accounting.coa.manage');
        return view('accounting.coa.create', ['account' => new ChartOfAccount(), 'parents' => ChartOfAccount::whereNull('parent_id')->orderBy('code')->get()]);
    }

    public function store(Request $request)
    {
        $this->gate('accounting.coa.manage');
        ChartOfAccount::create($request->validate($this->rules()));
        return redirect()->route('accounting.coa.index')->with('success', 'Account created.');
    }

    public function edit(ChartOfAccount $coa)
    {
        $this->gate('accounting.coa.manage');
        return view('accounting.coa.create', [
            'account' => $coa,
            'parents' => ChartOfAccount::whereNull('parent_id')->where('id', '!=', $coa->id)->orderBy('code')->get(),
        ]);
    }

    public function update(Request $request, ChartOfAccount $coa)
    {
        $this->gate('accounting.coa.manage');
        $coa->update($request->validate($this->rules($coa->id)));
        return redirect()->route('accounting.coa.index')->with('success', 'Account updated.');
    }

    public function destroy(ChartOfAccount $coa)
    {
        $this->gate('accounting.coa.manage');
        $coa->delete();
        return redirect()->route('accounting.coa.index')->with('success', 'Account deleted.');
    }

    private function rules(?int $id = null): array
    {
        return [
            'code' => ['required', 'string', 'max:32'],
            'name' => ['required', 'string', 'max:191'],
            'account_type' => ['required', \Illuminate\Validation\Rule::in(['asset', 'liability', 'equity', 'income', 'expense'])],
            'category' => ['nullable', 'string', 'max:64'],
            'parent_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'is_postable' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    private function gate(string $perm): void
    {
        if (! auth()->user()?->can($perm)) {
            abort(403, "Missing permission: {$perm}");
        }
    }
}
