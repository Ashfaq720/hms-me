<?php

namespace App\Http\Controllers\BloodBank;

use App\Http\Controllers\Controller;
use App\Models\BloodBank\BloodCollection;
use App\Models\BloodBank\BloodDonor;
use App\Models\BloodBank\BloodGroup;
use App\Models\BloodBank\BloodIssue;
use App\Models\BloodBank\Component;
use App\Models\BloodBank\ComponentCollection;
use App\Models\Charges\Charge;
use App\Models\Patient;
use App\Models\Doctor;

class BloodBankController extends Controller
{
    public function index()
    {
        $donors         = BloodDonor::where('is_active', true)->orderBy('name')->get();
        $allBloodGroups = BloodGroup::where('is_active', true)->get();
        $charges        = Charge::orderBy('charge_name')->get();
        $patients       = Patient::orderBy('patient_name')->get(['id', 'patient_name', 'mrn']);
        $doctors        = Doctor::orderBy('name')->get(['id', 'name']);

        // IDs already issued
        $issuedBloodIds     = BloodIssue::whereNotNull('blood_collection_id')->pluck('blood_collection_id');
        $issuedComponentIds = BloodIssue::whereNotNull('component_collection_id')->pluck('component_collection_id');

        // Blood collections grouped by blood group (exclude bags already used in component collections OR issued)
        $collections = BloodCollection::with(['donor', 'bloodGroup'])
            ->doesntHave('componentCollections')
            ->whereNotIn('id', $issuedBloodIds)
            ->latest('donate_date')->get();
        $bloodTotal  = $collections->count();

        // Group collections by blood_group_id, keyed by group id
        $collectionsByGroup = $collections->groupBy('blood_group_id');

        // Build summary per blood group (count + total volume)
        $groupSummary = [];
        foreach ($allBloodGroups as $bg) {
            $items = $collectionsByGroup->get($bg->id, collect());
            $groupSummary[] = [
                'id'      => $bg->id,
                'display' => $bg->combined,
                'count'   => $items->count(),
                'volume'  => $items->sum('volume'),
            ];
        }

        // Component collections (exclude already issued)
        $componentCollections = ComponentCollection::with(['component', 'bloodGroup', 'bloodCollection', 'donor'])
            ->whereNotIn('id', $issuedComponentIds)
            ->latest('datetime')->get();

        $componentTotal = $componentCollections->count();

        // All active components for the modal
        $allComponents = Component::where('is_active', true)->orderBy('component_name')->get();

        // Component collections grouped by blood_group_id
        $componentsByGroup = $componentCollections->groupBy('blood_group_id');

        // Component group summary (count per blood group)
        $componentGroupSummary = [];
        foreach ($allBloodGroups as $bg) {
            $items = $componentsByGroup->get($bg->id, collect());
            $componentGroupSummary[] = [
                'id'      => $bg->id,
                'display' => $bg->combined,
                'count'   => $items->count(),
            ];
        }

        return view('blood_bank.index', compact(
            'groupSummary', 'collectionsByGroup', 'bloodTotal',
            'collections',
            'componentGroupSummary', 'componentsByGroup', 'componentTotal', 'componentCollections',
            'allComponents',
            'donors', 'allBloodGroups', 'charges',
            'patients', 'doctors'
        ));
    }
}
