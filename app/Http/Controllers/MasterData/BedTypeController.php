<?php
namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\BedType;
use Illuminate\Http\Request;

class BedTypeController extends Controller
{
    public function index()
    {
        $bedTypes = BedType::all();
        return view('bed-type.index', compact('bedTypes'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $data = $this->validatedPayload($request);

        BedType::create($data);

        return redirect()->route('bed-types.index')
            ->with('success', 'Bed Type created successfully.');
    }

    public function show(BedType $bedType)
    {
        //
    }

    public function edit(BedType $bedType)
    {
        //
    }

    public function update(Request $request, BedType $bedType)
    {
        $data = $this->validatedPayload($request);

        $bedType->update($data);

        return redirect()->route('bed-types.index')
            ->with('success', 'Bed Type updated successfully.');
    }

    public function destroy(BedType $bedType)
    {
        //
    }

    private function validatedPayload(Request $request): array
    {
        $request->validate([
            'name'                   => 'required|string|max:255',
            'is_icu'                 => 'nullable|boolean',
            'icu_type'               => 'nullable|in:ICU,CCU,NICU,PICU',
            'has_ventilator_support' => 'nullable|boolean',
            'has_monitor_support'    => 'nullable|boolean',
            'is_isolation_bed'       => 'nullable|boolean',
            'allowed_isolation_type' => 'nullable|in:Airborne,Contact,Droplet,Standard',
        ]);

        $isIcu = (bool) $request->input('is_icu', false);

        return [
            'name'                   => $request->name,
            'is_icu'                 => $isIcu,
            'icu_type'               => $isIcu ? $request->input('icu_type') : null,
            'has_ventilator_support' => $isIcu ? (bool) $request->input('has_ventilator_support', false) : false,
            'has_monitor_support'    => $isIcu ? (bool) $request->input('has_monitor_support', false) : false,
            'is_isolation_bed'       => $isIcu ? (bool) $request->input('is_isolation_bed', false) : false,
            'allowed_isolation_type' => $isIcu ? $request->input('allowed_isolation_type') : null,
        ];
    }
}
