<?php

namespace App\Http\Controllers\NICU;

use App\Http\Controllers\Controller;
use App\Models\Nicu\NicuGrowthRecord;
use App\Models\NicuAdmission;
use Illuminate\Http\Request;

class NicuGrowthController extends Controller
{
    public function index(Request $request)
    {
        $this->gate('nicu_access');

        $q = NicuGrowthRecord::with(['admission.baby:id,patient_name', 'recordedBy:id,name']);
        if ($request->filled('admission_id')) {
            $q->where('nicu_admission_id', $request->get('admission_id'));
        }
        $records = $q->latest('measured_on')->paginate(30)->appends($request->query());
        $activeAdmissions = NicuAdmission::with('baby:id,patient_name')
            ->whereIn('status', [NicuAdmission::STATUS_ADMITTED, NicuAdmission::STATUS_IN_PROGRESS])
            ->orderBy('admission_no')->get();

        return view('nicu.growth.index', compact('records', 'activeAdmissions'));
    }

    public function store(Request $request)
    {
        $this->gate('nicu_edit');

        $data = $request->validate([
            'nicu_admission_id'     => ['required', 'exists:nicu_admissions,id'],
            'measured_on'           => ['required', 'date'],
            'weight_grams'          => ['nullable', 'numeric', 'min:100', 'max:10000'],
            'length_cm'             => ['nullable', 'numeric', 'min:20', 'max:80'],
            'head_circumference_cm' => ['nullable', 'numeric', 'min:15', 'max:60'],
            'notes'                 => ['nullable', 'string', 'max:2000'],
        ]);

        $data['recorded_by'] = auth()->id();
        NicuGrowthRecord::create($data);

        return back()->with('success', 'Growth record saved.');
    }

    protected function gate(string $permission): void
    {
        if (auth()->check() && ! auth()->user()->can($permission)) {
            abort(403, "Missing permission: {$permission}");
        }
    }
}
