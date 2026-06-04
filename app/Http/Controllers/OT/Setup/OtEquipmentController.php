<?php

namespace App\Http\Controllers\OT\Setup;

use App\Http\Controllers\Controller;
use App\Models\Ot\OtEquipment;
use App\Models\Ot\OtRoom;
use Illuminate\Http\Request;

class OtEquipmentController extends Controller
{
    public function index()
    {
        abort_if(! auth()->user()?->can('ot_setup_access') && ! auth()->user()?->hasRole('Super Admin'), 403);
        $equipments = OtEquipment::with('room')->orderBy('name')->paginate(30);

        return view('ot.setup.equipments.index', compact('equipments'));
    }

    public function create()
    {
        $rooms = OtRoom::active()->orderBy('name')->get();

        return view('ot.setup.equipments.create', compact('rooms'));
    }

    public function store(Request $request)
    {
        OtEquipment::create($this->validateData($request));

        return redirect()->route('ot.setup.equipments.index')->with('success', 'Equipment created.');
    }

    public function show($id)
    {
        $equipment = OtEquipment::with('room')->findOrFail($id);

        return view('ot.setup.equipments.show', compact('equipment'));
    }

    public function edit($id)
    {
        $equipment = OtEquipment::findOrFail($id);
        $rooms = OtRoom::active()->orderBy('name')->get();

        return view('ot.setup.equipments.edit', compact('equipment', 'rooms'));
    }

    public function update(Request $request, $id)
    {
        $equipment = OtEquipment::findOrFail($id);
        $equipment->update($this->validateData($request, $equipment->id));

        return redirect()->route('ot.setup.equipments.index')->with('success', 'Equipment updated.');
    }

    public function destroy($id)
    {
        OtEquipment::findOrFail($id)->delete();

        return back()->with('success', 'Equipment deleted.');
    }

    protected function validateData(Request $request, $excludeId = null): array
    {
        return $request->validate([
            'code' => 'required|string|max:50|unique:ot_equipments,code' . ($excludeId ? ",{$excludeId}" : ''),
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'ot_room_id' => 'nullable|exists:ot_rooms,id',
            'serial_no' => 'nullable|string|max:100',
            'status' => 'nullable|string|max:30',
            'last_service_date' => 'nullable|date',
            'next_service_date' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ]);
    }
}
