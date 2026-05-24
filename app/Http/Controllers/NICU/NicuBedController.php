<?php

namespace App\Http\Controllers\NICU;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use App\Models\BedType;
use App\Models\NicuAdmission;

class NicuBedController extends Controller
{
    /**
     * Incubator / Warmer view — read-only listing of NICU beds with
     * occupancy + which baby is in each one.
     */
    public function index()
    {
        if (auth()->check() && ! auth()->user()->can('nicu_access')) {
            abort(403, 'Missing permission: nicu_access');
        }

        $bedTypes = BedType::where(function ($q) {
            $q->where('is_icu', 1)
              ->orWhereIn('name', ['NICU', 'NICU Bed', 'Incubator', 'NICU Incubator', 'Warmer', 'NICU Warmer', 'New Born']);
        })->orderBy('name')->get();

        $beds = Bed::with(['bedType'])
            ->whereIn('bed_type_id', $bedTypes->pluck('id'))
            ->where('is_active', 1)
            ->orderBy('bed_type_id')->orderBy('name')->get();

        // Map current occupant per bed via NICU admissions
        $occupants = NicuAdmission::with('baby:id,patient_name,gender')
            ->whereIn('bed_id', $beds->pluck('id'))
            ->whereIn('status', [NicuAdmission::STATUS_ADMITTED, NicuAdmission::STATUS_IN_PROGRESS])
            ->get()->keyBy('bed_id');

        return view('nicu.beds.index', compact('bedTypes', 'beds', 'occupants'));
    }
}
