<?php

namespace App\Http\Controllers\BloodBank;

use App\Http\Controllers\Controller;
use App\Models\BloodBank\BloodIssue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BloodIssueController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type', 'blood');

        $query = BloodIssue::with(['patient', 'doctor', 'charge', 'createdBy']);

        if ($type === 'component') {
            $issues = $query->where('type', 'component')
                ->with(['componentCollection.component', 'componentCollection.bloodGroup'])
                ->latest('issue_datetime')->get();

            return view('blood_bank.component_issues', compact('issues'));
        }

        $issues = $query->where('type', 'blood')
            ->with(['bloodCollection.donor', 'bloodCollection.bloodGroup'])
            ->latest('issue_datetime')->get();

        return view('blood_bank.blood_issues', compact('issues'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'                    => 'required|in:blood,component',
            'blood_collection_id'     => 'nullable|required_if:type,blood|exists:blood_collections,id',
            'component_collection_id' => 'nullable|required_if:type,component|exists:component_collections,id',
            'patient_id'              => 'required|exists:patients,id',
            'case_id'                 => 'nullable|exists:case_references,id',
            'issue_datetime'          => 'required|date',
            'doctor_id'               => 'nullable|exists:doctors,id',
            'reference_name'          => 'nullable|string|max:255',
            'technician_name'         => 'nullable|string|max:255',
            'charge_id'              => 'nullable|exists:charges,id',
        ]);

        try {
            $validated['created_by'] = Auth::id();

            BloodIssue::create($validated);

            $label = $validated['type'] === 'blood' ? 'Blood' : 'Component';

            return redirect()->back()->with('success', "{$label} issued successfully.");
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Failed to issue: ' . $e->getMessage());
        }
    }
}
