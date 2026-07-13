<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use App\Models\Insurance\Payer;
use Illuminate\Http\Request;

class PayerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->gate('insurance.payer.view');
        $payers = Payer::orderBy('name')->paginate(20);
        return view('insurance.payers.index', compact('payers'));
    }

    public function create()
    {
        $this->gate('insurance.payer.manage');
        return view('insurance.payers.create', ['payer' => new Payer()]);
    }

    public function store(Request $request)
    {
        $this->gate('insurance.payer.manage');
        Payer::create($request->validate($this->rules()));
        return redirect()->route('insurance.payers.index')->with('success', 'Payer created.');
    }

    public function edit(Payer $payer)
    {
        $this->gate('insurance.payer.manage');
        return view('insurance.payers.create', compact('payer'));
    }

    public function update(Request $request, Payer $payer)
    {
        $this->gate('insurance.payer.manage');
        $payer->update($request->validate($this->rules($payer->id)));
        return redirect()->route('insurance.payers.index')->with('success', 'Payer updated.');
    }

    public function destroy(Payer $payer)
    {
        $this->gate('insurance.payer.manage');
        $payer->delete();
        return redirect()->route('insurance.payers.index')->with('success', 'Payer removed.');
    }

    private function rules(?int $id = null): array
    {
        return [
            'code' => ['required', 'string', 'max:32'],
            'name' => ['required', 'string', 'max:191'],
            'type' => ['required', \Illuminate\Validation\Rule::in(['insurance', 'corporate', 'government', 'tpa', 'self'])],
            'contact_person' => ['nullable', 'string', 'max:191'],
            'phone' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email'],
            'address' => ['nullable', 'string', 'max:500'],
            'default_discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'pre_auth_required' => ['nullable', 'boolean'],
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
