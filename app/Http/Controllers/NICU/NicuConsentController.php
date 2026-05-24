<?php

namespace App\Http\Controllers\NICU;

use App\Http\Controllers\Controller;
use App\Models\Nicu\NicuConsent;
use App\Models\NicuAdmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NicuConsentController extends Controller
{
    public function index(Request $request)
    {
        $this->gate('nicu_access');

        $q = NicuConsent::with(['admission.baby:id,patient_name', 'capturedBy:id,name']);
        if ($request->filled('admission_id')) {
            $q->where('nicu_admission_id', $request->get('admission_id'));
        }
        $consents = $q->latest('signed_at')->paginate(30)->appends($request->query());
        $activeAdmissions = NicuAdmission::with('baby:id,patient_name')
            ->whereIn('status', [NicuAdmission::STATUS_ADMITTED, NicuAdmission::STATUS_IN_PROGRESS])
            ->orderBy('admission_no')->get();

        return view('nicu.consents.index', compact('consents', 'activeAdmissions'));
    }

    public function store(Request $request)
    {
        $this->gate('nicu_edit');

        $data = $request->validate([
            'nicu_admission_id' => ['required', 'exists:nicu_admissions,id'],
            'consent_type'      => ['required', 'in:' . implode(',', NicuConsent::TYPES)],
            'signed_at'         => ['required', 'date'],
            'signed_by_name'    => ['required', 'string', 'max:200'],
            'relation_to_baby'  => ['nullable', 'string', 'max:60'],
            'witness_name'      => ['nullable', 'string', 'max:200'],
            'document'          => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'notes'             => ['nullable', 'string', 'max:2000'],
        ]);

        if ($request->hasFile('document')) {
            $data['document_path'] = $request->file('document')->store('nicu/consents', 'public');
        }
        unset($data['document']);
        $data['captured_by'] = auth()->id();
        NicuConsent::create($data);

        return back()->with('success', 'Consent saved.');
    }

    protected function gate(string $permission): void
    {
        if (auth()->check() && ! auth()->user()->can($permission)) {
            abort(403, "Missing permission: {$permission}");
        }
    }
}
