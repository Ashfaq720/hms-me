<?php

namespace App\Http\Controllers\NICU;

use App\Http\Controllers\Controller;
use App\Models\Nicu\NicuFeeding;
use App\Models\NicuAdmission;
use Illuminate\Http\Request;

class NicuFeedingController extends Controller
{
    public function index(Request $request)
    {
        $this->gate('nicu_access');

        $q = NicuFeeding::with(['admission.baby:id,patient_name', 'recordedBy:id,name']);
        if ($request->filled('admission_id')) {
            $q->where('nicu_admission_id', $request->get('admission_id'));
        }
        $feedings = $q->latest('fed_at')->paginate(30)->appends($request->query());
        $activeAdmissions = NicuAdmission::with('baby:id,patient_name')
            ->whereIn('status', [NicuAdmission::STATUS_ADMITTED, NicuAdmission::STATUS_IN_PROGRESS])
            ->orderBy('admission_no')->get();

        return view('nicu.feedings.index', compact('feedings', 'activeAdmissions'));
    }

    public function store(Request $request)
    {
        $this->gate('nicu_edit');

        $data = $request->validate([
            'nicu_admission_id' => ['required', 'exists:nicu_admissions,id'],
            'fed_at'            => ['required', 'date'],
            'feed_type'         => ['required', 'in:' . implode(',', NicuFeeding::FEED_TYPES)],
            'route'             => ['nullable', 'in:' . implode(',', NicuFeeding::ROUTES)],
            'volume_ml'         => ['nullable', 'numeric', 'min:0', 'max:500'],
            'tolerated'         => ['sometimes', 'boolean'],
            'vomited'           => ['sometimes', 'boolean'],
            'notes'             => ['nullable', 'string', 'max:2000'],
        ]);

        $data['tolerated'] = (bool) ($data['tolerated'] ?? true);
        $data['vomited']   = (bool) ($data['vomited']   ?? false);
        $data['recorded_by'] = auth()->id();
        NicuFeeding::create($data);

        return back()->with('success', 'Feeding logged.');
    }

    protected function gate(string $permission): void
    {
        if (auth()->check() && ! auth()->user()->can($permission)) {
            abort(403, "Missing permission: {$permission}");
        }
    }
}
