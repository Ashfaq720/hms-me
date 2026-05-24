<?php
namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\DoctorRequest;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Doctor;
use App\Models\Specialist;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DoctorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $doctors = Doctor::with(['department', 'designation', 'specialist'])->latest()->paginate(15);
        return view('doctors.index', compact('doctors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments  = Department::orderBy('name')->get();
        $specialists  = Specialist::orderBy('name')->get();
        $designations = Designation::orderBy('name')->get();

        return view('doctors.create', compact('departments', 'specialists', 'designations'));

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DoctorRequest $request)
    {

        // dd($request->all());
        $data = $request->validated();

        $data['is_active'] = (bool) ($request->input('is_active', 1));

        // upload doctor image (we will use same as user avatar)
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('doctors', 'public');
        }

        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();

        DB::beginTransaction();

        try {
            // 1) Create User first
            $user = User::create([
                'name'       => $data['name'],
                'email'      => $data['email'],
                'type'       => 'doctor',
                'phone'      => $data['phone'],
                'avatar'     => $data['image'] ?? null,
                'password'   => Hash::make('123456'),
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);
            // 2) Set user_id for doctor
            $data['user_id'] = $user->id;

            // 3) Create Doctor
            $doctor = Doctor::create($data);

            DB::commit();

            return redirect()
                ->route('doctors.index')
                ->with('success', 'Doctor created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Doctor create failed! ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $doctor = Doctor::with(['department', 'designation', 'specialist'])->findOrFail($id);
        return view('doctors.show', compact('doctor'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $departments  = Department::orderBy('name')->get();
        $specialists  = Specialist::orderBy('name')->get();
        $designations = Designation::orderBy('name')->get();
        $doctor       = Doctor::with(['department', 'designation', 'specialist'])->findOrFail($id);
        return view('doctors.edit', compact('doctor', 'departments', 'specialists', 'designations'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DoctorRequest $request, Doctor $doctor)
    {
        $data               = $request->validated();
        $data['is_active']  = (bool) ($request->input('is_active', 1));
        $data['updated_by'] = Auth::id();

        DB::beginTransaction();

        $oldImage = $doctor->image; // keep old image path
        $newImage = null;

        try {

            // Upload new image first (do NOT delete old yet)
            if ($request->hasFile('image')) {
                $newImage      = $request->file('image')->store('doctors', 'public');
                $data['image'] = $newImage;
            }

            // Update doctor
            $doctor->update($data);

            DB::commit();

            // After commit, delete old image (only if new uploaded)
            if ($newImage && $oldImage && Storage::disk('public')->exists($oldImage)) {
                Storage::disk('public')->delete($oldImage);
            }

            return redirect()
                ->route('doctors.index')
                ->with('success', 'Doctor updated successfully.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Doctor update failed! ' . $e->getMessage());
        }
    }

/**
 * Remove the specified resource from storage.
 */
    public function destroy(Doctor $doctor)
    {
        if ($doctor->image && Storage::disk('public')->exists($doctor->image)) {
            Storage::disk('public')->delete($doctor->image);
        }

        $doctor->delete();

        return redirect()
            ->route('doctors.index')
            ->with('success', 'Doctor deleted successfully.');
    }
}
