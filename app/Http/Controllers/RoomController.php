<?php

namespace App\Http\Controllers;

use App\Models\Floor;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rooms  = Room::with('floor')->latest('id')->get();
        $floors = Floor::orderBy('name')->get();
        return view('room.index', compact('rooms', 'floors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'floor_id'  => 'required|exists:floors,id',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true) ? 1 : 0;

        Room::create($data);

        return redirect()->route('rooms.index')
            ->with('success', 'Room created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Room $room)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'floor_id'  => 'required|exists:floors,id',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true) ? 1 : 0;

        $room->update($data);

        return redirect()->route('rooms.index')
            ->with('success', 'Room updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Room $room)
    {
        $room->delete();
        return redirect()->route('rooms.index')
            ->with('success', 'Room deleted successfully.');
    }
}
