<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Charges\ChargeCategory;
use App\Models\Doctor;
use App\Models\DoctorSlotSetting;
use App\Models\DoctorSlotTime;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DoctorSlotController extends Controller
{
    private array $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    public function index()
    {
        $doctors          = Doctor::orderBy('name')->get(['id', 'name', 'doctor_code']);
        $shifts           = Shift::where('is_active', 1)->orderBy('id')->get(['id', 'name']);
        $chargeCategories = ChargeCategory::orderBy('name')->get(['id', 'name']);
        $days             = $this->days;

        return view('doctor-slots.index', compact('doctors', 'shifts', 'chargeCategories', 'days'));
    }

    public function fetch(Request $request)
    {
        $data = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'shift_id'  => 'required|exists:shifts,id',
        ]);

        $setting = DoctorSlotSetting::where('doctor_id', $data['doctor_id'])
            ->where('shift_id', $data['shift_id'])
            ->first();

        $times = DoctorSlotTime::where('doctor_id', $data['doctor_id'])
            ->where('shift_id', $data['shift_id'])
            ->orderBy('time_from')
            ->get();

        $grouped = collect($this->days)->mapWithKeys(function ($day) use ($times) {
            return [
                $day => $times->where('day', $day)->map(function ($t) {
                    return [
                        'time_from' => substr($t->time_from, 0, 5),
                        'time_to'   => substr($t->time_to, 0, 5),
                    ];
                })->values(),
            ];
        });

        return response()->json([
            'setting' => $setting,
            'times'   => $grouped,
        ]);
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'doctor_id'            => 'required|exists:doctors,id',
            'shift_id'             => 'required|exists:shifts,id',
            'consultation_minutes' => 'required|integer|min:1',
            'charge_category_id'   => 'nullable|exists:charge_categories,id',
            'charge_id'            => 'nullable|exists:charges,id',
            'amount'               => 'nullable|numeric|min:0',
            'times'                => 'array',
            'times.*.day'          => 'required|in:' . implode(',', $this->days),
            'times.*.time_from'    => 'required|date_format:H:i',
            'times.*.time_to'      => 'required|date_format:H:i|after:times.*.time_from',
        ]);

        DB::transaction(function () use ($data) {
            DoctorSlotSetting::updateOrCreate(
                ['doctor_id' => $data['doctor_id'], 'shift_id' => $data['shift_id']],
                [
                    'consultation_minutes' => $data['consultation_minutes'],
                    'charge_category_id'   => $data['charge_category_id'] ?? null,
                    'charge_id'            => $data['charge_id'] ?? null,
                    'amount'               => $data['amount'] ?? 0,
                ]
            );

            DoctorSlotTime::where('doctor_id', $data['doctor_id'])
                ->where('shift_id', $data['shift_id'])
                ->delete();

            foreach ($data['times'] ?? [] as $t) {
                DoctorSlotTime::create([
                    'doctor_id' => $data['doctor_id'],
                    'shift_id'  => $data['shift_id'],
                    'day'       => $t['day'],
                    'time_from' => $t['time_from'],
                    'time_to'   => $t['time_to'],
                ]);
            }
        });

        return response()->json(['success' => true]);
    }
}
