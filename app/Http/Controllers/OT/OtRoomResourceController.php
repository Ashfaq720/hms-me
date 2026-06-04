<?php

namespace App\Http\Controllers\OT;

use App\Http\Controllers\Controller;
use App\Models\Ot\OtEquipment;
use App\Models\Ot\OtRoom;
use App\Models\Ot\OtSurgerySchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OtRoomResourceController extends OtBaseController
{
    public function index()
    {
        $this->gate('ot_room_access');
        $rooms = OtRoom::with('equipments')->orderBy('name')->get();

        return view('ot.rooms.index', compact('rooms'));
    }

    public function create()
    {
        $floors = \App\Models\Floor::orderBy('name')->get();
        return view('ot.rooms.create', compact('floors'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedRoom($request);
        $room = OtRoom::create($data);

        return redirect()->route('ot.rooms.index')->with('success', 'Room created.');
    }

    public function show($id)
    {
        $room = OtRoom::with('equipments')->findOrFail($id);
        $upcoming = OtSurgerySchedule::with('surgeryRequest.patient')
            ->where('ot_room_id', $room->id)
            ->whereNotIn('status', [OtSurgerySchedule::STATUS_CLOSED, OtSurgerySchedule::STATUS_CANCELLED])
            ->orderBy('scheduled_start')
            ->take(10)
            ->get();

        return view('ot.rooms.show', compact('room', 'upcoming'));
    }

    public function edit($id)
    {
        $room = OtRoom::findOrFail($id);
        $floors = \App\Models\Floor::orderBy('name')->get();

        return view('ot.rooms.edit', compact('room', 'floors'));
    }

    public function update(Request $request, $id)
    {
        $room = OtRoom::findOrFail($id);
        $room->update($this->validatedRoom($request, $room->id));

        return redirect()->route('ot.rooms.index')->with('success', 'Room updated.');
    }

    public function destroy($id)
    {
        $room = OtRoom::findOrFail($id);
        $room->delete();

        return back()->with('success', 'Room deleted.');
    }

    public function roomStatus(Request $request)
    {
        $today = Carbon::today();
        $rooms = OtRoom::active()->orderBy('name')->get()->map(function ($room) use ($today) {
            $room->today_count = OtSurgerySchedule::where('ot_room_id', $room->id)
                ->whereDate('scheduled_start', $today)
                ->whereNotIn('status', [OtSurgerySchedule::STATUS_CANCELLED])
                ->count();
            $room->running = OtSurgerySchedule::where('ot_room_id', $room->id)
                ->whereIn('status', [OtSurgerySchedule::STATUS_SURGERY_RUNNING, OtSurgerySchedule::STATUS_ANESTHESIA_STARTED])
                ->first();
            return $room;
        });

        return view('ot.rooms.status', compact('rooms'));
    }

    protected function validatedRoom(Request $request, $excludeId = null): array
    {
        return $request->validate([
            'code' => 'required|string|max:30|unique:ot_rooms,code' . ($excludeId ? ",{$excludeId}" : ''),
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:50',
            'floor_id' => 'nullable|exists:floors,id',
            'block' => 'nullable|string|max:100',
            'is_emergency' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'status' => 'nullable|string|max:30',
            'description' => 'nullable|string',
        ]);
    }
}
