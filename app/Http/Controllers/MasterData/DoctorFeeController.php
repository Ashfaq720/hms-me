<?php
namespace App\Http\Controllers\MasterData;

use App\Models\Doctor;
use App\Models\DoctorFee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;


class DoctorFeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data    = DoctorFee::all();
        $doctors = Doctor::select('id', 'name')->get();

        return view('doctor-fees.index', compact('data', 'doctors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'doctor_id'        => ['required', 'integer', 'unique:doctor_fees,doctor_id,', 'exists:doctors,id'],
            'first_visit_fee'  => ['nullable', 'numeric'],
            'follow_up_window' => ['nullable', 'integer'],
            'follow_up_fee'    => ['nullable', 'numeric'],
            'ipd_visit_fee'    => ['nullable', 'numeric'],
            'opd_visit_fee'    => ['nullable', 'numeric'],
        ]);

        DB::beginTransaction();

        try {

            DoctorFee::create([
                'doctor_id'        => $validated['doctor_id'],
                'first_visit_fee'  => $validated['first_visit_fee'],
                'follow_up_window' => $validated['follow_up_window'],
                'follow_up_fee'    => $validated['follow_up_fee'],
                'ipd_visit_fee'    => $validated['ipd_visit_fee'],
                'opd_visit_fee'    => $validated['opd_visit_fee'],
            ]);

            DB::commit();

            return redirect()
                ->route('doctor-fees.index')
                ->with('success', 'Doctor Fee created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Failed to create Doctor Fee. ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(DoctorFee $doctorFee)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DoctorFee $doctorFee)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DoctorFee $doctorFee)
    {
        $validated = $request->validate([
            'doctor_id'        => ['required', 'integer', 'unique:doctor_fees,doctor_id,' . $doctorFee->id, 'exists:doctors,id'],
            'first_visit_fee'  => ['nullable', 'numeric'],
            'follow_up_window' => ['nullable', 'integer'],
            'follow_up_fee'    => ['nullable', 'numeric'],
            'ipd_visit_fee'    => ['nullable', 'numeric'],
            'opd_visit_fee'    => ['nullable', 'numeric'],
        ]);

        DB::beginTransaction();

        try {

            $doctorFee->update([
                'doctor_id'        => $validated['doctor_id'],
                'first_visit_fee'  => $validated['first_visit_fee'],
                'follow_up_window' => $validated['follow_up_window'],
                'follow_up_fee'    => $validated['follow_up_fee'],
                'ipd_visit_fee'    => $validated['ipd_visit_fee'],
                'opd_visit_fee'    => $validated['opd_visit_fee'],
            ]);

            DB::commit();

            return redirect()
                ->route('doctor-fees.index')
                ->with('success', 'Doctor Fee updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Failed to update Doctor Fee. ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DoctorFee $doctorFee)
    {
        $doctorFee->delete();
        return redirect()->route('doctor-fees.index')->with('success', 'Doctor Fee deleted successfully.');
    }
}
