<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Organization\Branch;
use App\Models\Organization\Organization;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->gate('branch.view');
        $branches = Branch::with('organization')->orderBy('name')->paginate(20);
        return view('branches.index', compact('branches'));
    }

    public function create()
    {
        $this->gate('branch.manage');
        $organizations = Organization::orderBy('name')->get();
        return view('branches.create', ['branch' => new Branch(), 'organizations' => $organizations]);
    }

    public function store(Request $request)
    {
        $this->gate('branch.manage');
        $data = $request->validate($this->rules());
        Branch::create($data);
        return redirect()->route('branches.index')->with('success', 'Branch created.');
    }

    public function edit(Branch $branch)
    {
        $this->gate('branch.manage');
        $organizations = Organization::orderBy('name')->get();
        return view('branches.create', compact('branch', 'organizations'));
    }

    public function update(Request $request, Branch $branch)
    {
        $this->gate('branch.manage');
        $data = $request->validate($this->rules($branch->id));
        $branch->update($data);
        return redirect()->route('branches.index')->with('success', 'Branch updated.');
    }

    public function destroy(Branch $branch)
    {
        $this->gate('branch.manage');
        $branch->delete();
        return redirect()->route('branches.index')->with('success', 'Branch archived.');
    }

    private function rules(?int $id = null): array
    {
        return [
            'organization_id' => ['required', 'exists:organizations,id'],
            'code' => ['required', 'string', 'max:32'],
            'name' => ['required', 'string', 'max:191'],
            'type' => ['nullable', 'string', 'max:32'],
            'address_line1' => ['nullable', 'string', 'max:191'],
            'city' => ['nullable', 'string', 'max:64'],
            'country' => ['nullable', 'string', 'max:2'],
            'phone' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email'],
            'mrn_prefix' => ['nullable', 'string', 'max:16'],
            'invoice_prefix' => ['nullable', 'string', 'max:16'],
            'health_card_prefix' => ['nullable', 'string', 'max:16'],
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
