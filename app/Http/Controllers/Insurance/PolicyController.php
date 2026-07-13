<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use App\Models\Insurance\InsurancePolicy;

class PolicyController extends Controller
{
    public function index()
    {
        $policies = InsurancePolicy::with(['payer', 'patient'])
            ->latest('id')->paginate(25);
        return view('insurance.policies.index', compact('policies'));
    }

    public function show($id)
    {
        $policy = InsurancePolicy::with(['payer', 'patient'])->findOrFail($id);
        return view('insurance.policies.show', compact('policy'));
    }
}
