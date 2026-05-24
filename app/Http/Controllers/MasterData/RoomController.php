<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\BedGroup;
use App\Models\Floor;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $q = Room::with(['bedGroup', 'floor', 'beds'])
            ->when($request->floor_id, fn ($qq) => $qq->where('floor_id', $request->floor_id))
            ->when($request->bed_group_id, fn ($qq) => $qq->where('bed_group_id', $request->bed_group_id))
            ->when($request->room_class, fn ($qq) => $qq->where('room_class', $request->room_class))
            ->when($request->q, fn ($qq, $s) => $qq->where(function ($w) use ($s) {
                $w->where('room_no', 'like', "%{$s}%")
                  ->orWhere('name', 'like', "%{$s}%");
            }))
            ->orderBy('floor_id')->orderBy('room_no');

        $rooms     = $q->paginate(25)->withQueryString();
        $floors    = Floor::orderBy('name')->get();
        $bedGroups = BedGroup::orderBy('name')->get();

        return view('rooms.index', compact('rooms', 'floors', 'bedGroups'));
    }

    public function create()
    {
        return view('rooms._form', [
            'room'      => new Room(['capacity' => 1, 'is_active' => true]),
            'floors'    => Floor::where('is_active', true)->orderBy('name')->get(),
            'bedGroups' => BedGroup::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        Room::create($data);
        return redirect()->route('rooms.index')->with('success', 'Room added.');
    }

    public function edit(Room $room)
    {
        return view('rooms._form', [
            'room'      => $room,
            'floors'    => Floor::orderBy('name')->get(),
            'bedGroups' => BedGroup::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Room $room)
    {
        $room->update($this->validated($request, $room));
        return redirect()->route('rooms.index')->with('success', 'Room updated.');
    }

    public function destroy(Room $room)
    {
        if ($room->beds()->exists()) {
            return back()->with('error', 'Cannot delete — beds are linked to this room.');
        }
        $room->delete();
        return back()->with('success', 'Room deleted.');
    }

    protected function validated(Request $request, ?Room $room = null): array
    {
        $rules = [
            'bed_group_id'        => 'required|exists:bed_groups,id',
            'floor_id'            => 'nullable|exists:floors,id',
            'room_no'             => 'required|string|max:50',
            'name'                => 'nullable|string|max:100',
            'room_class'          => 'required|in:' . implode(',', array_keys(Room::CLASSES)),
            'capacity'            => 'required|integer|min:1|max:20',
            'room_rent'           => 'required|numeric|min:0',
            'has_ac'              => 'sometimes|boolean',
            'has_attached_bath'   => 'sometimes|boolean',
            'has_tv'              => 'sometimes|boolean',
            'has_fridge'          => 'sometimes|boolean',
            'has_sofa_cum_bed'    => 'sometimes|boolean',
            'has_oxygen_outlet'   => 'sometimes|boolean',
            'has_central_monitor' => 'sometimes|boolean',
            'description'         => 'nullable|string|max:500',
            'is_active'           => 'sometimes|boolean',
        ];
        $data = $request->validate($rules);
        foreach (['has_ac', 'has_attached_bath', 'has_tv', 'has_fridge', 'has_sofa_cum_bed', 'has_oxygen_outlet', 'has_central_monitor', 'is_active'] as $b) {
            $data[$b] = (bool) ($request->boolean($b));
        }
        return $data;
    }
}
