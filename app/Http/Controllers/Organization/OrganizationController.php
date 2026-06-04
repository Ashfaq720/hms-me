<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Organization\Organization;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->gate('organization.view');
        $organizations = Organization::orderBy('name')->paginate(20);
        return view('organizations.index', compact('organizations'));
    }

    public function create()
    {
        $this->gate('organization.manage');
        return view('organizations.create', ['organization' => new Organization()]);
    }

    public function store(Request $request)
    {
        $this->gate('organization.manage');
        $data = $request->validate($this->rules());
        Organization::create($data);
        return redirect()->route('organizations.index')->with('success', 'Organization created.');
    }

    public function edit(Organization $organization)
    {
        $this->gate('organization.manage');
        return view('organizations.create', compact('organization'));
    }

    public function update(Request $request, Organization $organization)
    {
        $this->gate('organization.manage');
        $data = $request->validate($this->rules($organization->id));
        $organization->update($data);
        return redirect()->route('organizations.index')->with('success', 'Organization updated.');
    }

    public function destroy(Organization $organization)
    {
        $this->gate('organization.manage');
        $organization->delete();
        return redirect()->route('organizations.index')->with('success', 'Organization archived.');
    }

    private function rules(?int $id = null): array
    {
        return [
            'code' => ['required', 'string', 'max:32', \Illuminate\Validation\Rule::unique('organizations')->ignore($id)],
            'name' => ['required', 'string', 'max:191'],
            'legal_name' => ['nullable', 'string', 'max:191'],
            'contact_email' => ['nullable', 'email'],
            'contact_phone' => ['nullable', 'string', 'max:32'],
            'tax_number' => ['nullable', 'string', 'max:64'],
            'country' => ['nullable', 'string', 'max:2'],
            'timezone' => ['nullable', 'string', 'max:64'],
            'default_currency' => ['nullable', 'string', 'max:3'],
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
