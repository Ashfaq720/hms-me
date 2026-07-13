<?php

namespace App\Http\Controllers\ICU;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use App\Models\Icu\IcuBedEquipmentMapping;
use App\Models\Icu\IcuEquipment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IcuEquipmentController extends Controller
{
    public function index(Request $request)
    {
        $query = IcuEquipment::with('defaultBed.bedType')->latest('id');

        if ($request->filled('type')) {
            $query->where('equipment_type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('icu_type')) {
            $query->where('icu_type', $request->icu_type);
        }

        $equipment = $query->get();

        return view('icu.equipment.index', compact('equipment'));
    }

    public function create(Request $request)
    {
        $icuType = $request->input('icu_type');
        $beds = Bed::with('bedType')
            ->whereHas('bedType', function ($q) use ($icuType) {
                $q->where('is_icu', true)->when($icuType, fn($s) => $s->where('icu_type', $icuType));
            })
            ->get();

        return view('icu.equipment.create', compact('beds', 'icuType'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedPayload($request);
        IcuEquipment::create($data);

        return redirect()->route('icu.equipment.index', array_filter(['icu_type' => $request->input('icu_type')]))
            ->with('success', 'Equipment created.');
    }

    public function edit(Request $request, $id)
    {
        $item    = IcuEquipment::findOrFail($id);
        $icuType = $request->input('icu_type', $item->icu_type);

        $beds = Bed::with('bedType')
            ->whereHas('bedType', function ($q) use ($icuType) {
                $q->where('is_icu', true)->when($icuType, fn($s) => $s->where('icu_type', $icuType));
            })
            ->get();

        return view('icu.equipment.edit', compact('item', 'beds', 'icuType'));
    }

    public function update(Request $request, $id)
    {
        $item = IcuEquipment::findOrFail($id);
        $data = $this->validatedPayload($request, $item->id);
        $item->update($data);

        return redirect()->route('icu.equipment.index', array_filter(['icu_type' => $request->input('icu_type')]))
            ->with('success', 'Equipment updated.');
    }

    public function destroy(Request $request, $id)
    {
        $item = IcuEquipment::findOrFail($id);

        if ($item->status === 'InUse') {
            return back()->with('error', 'Cannot delete equipment that is currently in use.');
        }

        $item->delete();
        return redirect()->route('icu.equipment.index', array_filter(['icu_type' => $request->input('icu_type')]))
            ->with('success', 'Equipment deleted.');
    }

    /**
     * Attach equipment as default to a bed (master-data action).
     */
    public function attachToBed(Request $request, $id)
    {
        $request->validate([
            'bed_id' => ['required', 'integer', Rule::exists('beds', 'id')],
        ]);

        IcuBedEquipmentMapping::updateOrCreate(
            ['bed_id' => $request->bed_id, 'equipment_id' => $id],
            ['is_default' => true, 'status' => 'Active']
        );

        return back()->with('success', 'Equipment attached to bed.');
    }

    public function detachFromBed($id, $bedId)
    {
        IcuBedEquipmentMapping::where('equipment_id', $id)
            ->where('bed_id', $bedId)
            ->update(['status' => 'Detached']);

        return back()->with('success', 'Equipment detached from bed.');
    }

    private function validatedPayload(Request $request, ?int $ignoreId = null): array
    {
        $rules = [
            'equipment_code' => [
                'required', 'string', 'max:50',
                Rule::unique('icu_equipment', 'equipment_code')->ignore($ignoreId),
            ],
            'equipment_name' => ['required', 'string', 'max:150'],
            'equipment_type' => ['required', Rule::in([
                'Ventilator', 'Monitor', 'InfusionPump', 'SyringePump', 'OxygenSupport',
                'DialysisMachine', 'ECG', 'PulseOximeter', 'TemperatureSensor', 'Other',
            ])],
            'icu_type'       => ['nullable', Rule::in(['ICU', 'CCU', 'NICU', 'PICU'])],
            'serial_no'      => ['nullable', 'string', 'max:100'],
            'status'         => ['required', Rule::in([
                'Available', 'InUse', 'Maintenance', 'Cleaning', 'Damaged', 'Reserved',
            ])],
            'location'       => ['nullable', 'string', 'max:100'],
            'default_bed_id' => ['nullable', 'integer', Rule::exists('beds', 'id')],
            'charge_type'    => ['required', Rule::in(['Hour', 'Day', 'Session', 'Fixed'])],
            'charge_rate'    => ['required', 'numeric', 'min:0'],
            'is_active'      => ['nullable', 'boolean'],
            'remarks'        => ['nullable', 'string', 'max:1000'],
        ];

        $data = $request->validate($rules);
        $data['is_active'] = (bool) $request->input('is_active', true);

        return $data;
    }
}
