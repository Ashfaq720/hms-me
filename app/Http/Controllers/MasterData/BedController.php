<?php
namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use App\Models\BedGroup;
use App\Models\BedType;
use App\Models\Package;
use App\Models\Room;
use Illuminate\Http\Request;

class BedController extends Controller
{
    public function index()
    {
        $data      = Bed::with(['bedType', 'bedGroup.floor', 'room', 'defaultPackage'])
                        ->orderBy('name')->paginate(25)->withQueryString();
        $bedTypes  = BedType::orderBy('name')->get();
        $bedGroups = BedGroup::with('floor')->orderBy('name')->get();
        $rooms     = Room::with('bedGroup')->where('is_active', true)->orderBy('room_no')->get();
        $packages  = Package::where('is_active', true)->orderBy('name')->get(['id', 'name', 'package_type']);

        return view('bed.index', compact('data', 'bedTypes', 'bedGroups', 'rooms', 'packages'));
    }

    public function create()
    {
        // modal-based — never reached
        return redirect()->route('beds.index');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        Bed::create($data);
        return redirect()->route('beds.index')->with('success', 'Bed created successfully.');
    }

    public function show(Bed $bed)
    {
        return redirect()->route('beds.index');
    }

    public function edit(Bed $bed)
    {
        return redirect()->route('beds.index');
    }

    public function update(Request $request, Bed $bed)
    {
        $bed->update($this->validated($request, $bed));
        return redirect()->route('beds.index')->with('success', 'Bed updated successfully.');
    }

    public function destroy(Bed $bed)
    {
        $bed->delete();
        return redirect()->route('beds.index')->with('success', 'Bed deleted successfully.');
    }

    protected function validated(Request $request, ?Bed $bed = null): array
    {
        $data = $request->validate([
            'name'               => 'required|string|max:255',
            'bed_no'             => 'nullable|string|max:20',
            'rent'               => 'required|numeric|min:0',
            'amenity_charge'     => 'nullable|numeric|min:0',
            'nursing_charge'     => 'nullable|numeric|min:0',
            'bed_type_id'        => 'required|exists:bed_types,id',
            'bed_group_id'       => 'required|exists:bed_groups,id',
            'room_id'            => 'nullable|exists:rooms,id',
            'is_reserved'        => 'required|boolean',
            'status'             => 'nullable|in:' . implode(',', array_keys(Bed::STATUSES)),
            'default_package_id' => 'nullable|exists:packages,id',
        ]);
        $data['amenity_charge']     ??= 0;
        $data['nursing_charge']     ??= 0;
        $data['status']             ??= 'available';
        $data['default_package_id'] = $data['default_package_id'] ?: null;
        $data['room_id']            = $data['room_id'] ?: null;
        return $data;
    }
}
