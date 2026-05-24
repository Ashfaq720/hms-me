<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\OpdPatient;
use App\Models\Pharmacy\Medicine;
use App\Models\Pharmacy\MedicineBatch;
use App\Models\Pharmacy\OpdDispense;
use App\Models\Pharmacy\OpdDispenseItem;
use App\Models\Prescription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OpdDispenseController extends Controller
{
    public function index(Request $request)
    {
        $query = OpdDispense::with(['opdPatient', 'prescription', 'patient', 'pharmacist'])
            ->latest();

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereDate('created_at', '>=', $request->date_from)
                  ->whereDate('created_at', '<=', $request->date_to);
        } elseif ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('opd_no')) {
            $query->whereHas('opdPatient', fn($q) => $q->where('case_id', 'like', '%' . $request->opd_no . '%'));
        }

        if ($request->filled('patient_name')) {
            $query->whereHas('patient', fn($q) => $q->where('patient_name', 'like', '%' . $request->patient_name . '%'));
        }

        if ($request->filled('prescription_no')) {
            $query->whereHas('prescription', fn($q) => $q->where('prescription_no', 'like', '%' . $request->prescription_no . '%'));
        }

        if ($request->filled('pharmacist_id')) {
            $query->where('pharmacist_id', $request->pharmacist_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $dispenses = $query->get();

        $today = Carbon::today();
        $todayDispenses = OpdDispense::whereDate('created_at', $today);
        $todayTotal  = (clone $todayDispenses)->where('status', 'completed')->sum('total_amount');
        $todayCount  = (clone $todayDispenses)->where('status', 'completed')->count();
        $pendingCount = OpdDispense::where('status', 'pending_approval')->count();
        $partialCount = OpdDispense::where('status', 'partial')->count();
        $returnCount  = OpdDispense::where('status', 'cancelled')->whereDate('created_at', $today)->count();

        $pharmacists = User::whereIn('id', OpdDispense::distinct()->pluck('pharmacist_id'))
            ->orderBy('name')->get();

        return view('pharmacy.opd-dispense.index', compact(
            'dispenses', 'todayTotal', 'todayCount',
            'pendingCount', 'partialCount', 'returnCount', 'pharmacists'
        ));
    }

    public function create()
    {
        $lastId      = OpdDispense::max('id') ?? 0;
        $dispenseNo  = 'OPD-' . str_pad($lastId + 1, 6, '0', STR_PAD_LEFT);

        $opdPatients = OpdPatient::with('patient')
            ->orderByDesc('id')
            ->get();

        $prescriptions = Prescription::whereNotNull('opd_patient_id')
            ->orderByDesc('id')->get();

        $medicines = Medicine::where('status', 1)->orderBy('medicine_name')->get();
        $users     = User::orderBy('name')->get();

        $stores = MedicineBatch::where('status', 1)
            ->distinct()->pluck('store')->filter()->sort()->values();

        return view('pharmacy.opd-dispense.create', compact(
            'dispenseNo', 'opdPatients', 'prescriptions', 'medicines', 'users', 'stores'
        ));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'opd_patient_id'        => 'required|exists:opd_patients,id',
            'prescription_id'       => 'nullable|exists:prescriptions,id',
            'request_source'        => 'required|string',
            'payment_status'        => 'required|in:paid,unpaid,partial',
            'items'                 => 'required|array|min:1',
            'items.*.medicine_id'   => 'required|exists:medicines,id',
            'items.*.dosage'        => 'required|string',
            'items.*.qty_required'  => 'required|integer|min:1',
            'items.*.store'         => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.pharmacy.opd-dispense')
                ->withErrors($validator)
                ->withInput()
                ->with('show_create_modal', true);
        }

        $opdPatient = OpdPatient::findOrFail($request->opd_patient_id);

        $lastId     = OpdDispense::max('id') ?? 0;
        $dispenseNo = 'OPDDSP-' . str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);

        DB::transaction(function () use ($request, $opdPatient, $dispenseNo) {
            $dispense = OpdDispense::create([
                'dispense_no'    => $dispenseNo,
                'opd_patient_id' => $opdPatient->id,
                'prescription_id'=> $request->prescription_id,
                'patient_id'     => $opdPatient->patient_id,
                'pharmacist_id'  => auth()->id(),
                'drug_count'     => count($request->items),
                'total_amount'   => 0,
                'payment_status' => $request->payment_status,
                'status'         => 'pending_approval',
                'note'           => $request->remarks,
            ]);

            $totalAmount = 0;

            foreach ($request->items as $item) {
                $medicine     = Medicine::find($item['medicine_id']);
                $availableQty = $medicine->available_qty ?? 0;

                $batch = MedicineBatch::where('medicine_id', $item['medicine_id'])
                    ->where('store', $item['store'])
                    ->where('quantity', '>', 0)
                    ->orderBy('expiry_date')
                    ->first();

                $unitPrice    = $batch->selling_price ?? 0;
                $totalAmount += $unitPrice * $item['qty_required'];

                OpdDispenseItem::create([
                    'opd_dispense_id' => $dispense->id,
                    'medicine_id'     => $item['medicine_id'],
                    'dosage'          => $item['dosage'],
                    'qty_required'    => $item['qty_required'],
                    'available_qty'   => $availableQty,
                    'unit_price'      => $unitPrice,
                    'store'           => $item['store'],
                ]);
            }

            $dispense->update(['total_amount' => $totalAmount]);
        });

        return redirect()->route('admin.pharmacy.opd-dispense')
            ->with('success', 'OPD Dispense created successfully.');
    }

    public function show($id)
    {
        $dispense = OpdDispense::with([
            'opdPatient.patient',
            'prescription',
            'patient',
            'pharmacist',
            'items.medicine',
        ])->findOrFail($id);

        return view('pharmacy.opd-dispense.show', compact('dispense'));
    }

    public function export(Request $request)
    {
        $dispenses = $this->buildFilteredQuery($request)
            ->with(['opdPatient', 'prescription', 'patient', 'pharmacist'])
            ->get();

        $filename = 'opd-dispenses-' . now()->format('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($dispenses) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Dispense #', 'Date', 'OPD Case No', 'Patient', 'Prescription No',
                'Drug Count', 'Total Amount (TK)', 'Payment Status', 'Status', 'Pharmacist', 'Note',
            ]);

            foreach ($dispenses as $d) {
                fputcsv($handle, [
                    $d->dispense_no,
                    $d->created_at->format('d/m/Y'),
                    $d->opdPatient->case_id ?? '',
                    $d->patient->patient_name ?? '',
                    $d->prescription->prescription_no ?? '',
                    $d->drug_count,
                    number_format($d->total_amount, 2),
                    $d->payment_status,
                    $d->status,
                    $d->pharmacist->name ?? '',
                    $d->note ?? '',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function print(Request $request)
    {
        $dispenses = $this->buildFilteredQuery($request)
            ->with(['opdPatient', 'prescription', 'patient', 'pharmacist', 'items.medicine'])
            ->get();

        $filters = $request->only([
            'date_from', 'date_to', 'opd_no', 'patient_name',
            'prescription_no', 'pharmacist_id', 'status', 'payment_status',
        ]);

        return view('pharmacy.opd-dispense.print', compact('dispenses', 'filters'));
    }

    private function buildFilteredQuery(Request $request)
    {
        $query = OpdDispense::latest();

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereDate('created_at', '>=', $request->date_from)
                  ->whereDate('created_at', '<=', $request->date_to);
        } elseif ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('opd_no')) {
            $query->whereHas('opdPatient', fn($q) => $q->where('case_id', 'like', '%' . $request->opd_no . '%'));
        }

        if ($request->filled('patient_name')) {
            $query->whereHas('patient', fn($q) => $q->where('patient_name', 'like', '%' . $request->patient_name . '%'));
        }

        if ($request->filled('prescription_no')) {
            $query->whereHas('prescription', fn($q) => $q->where('prescription_no', 'like', '%' . $request->prescription_no . '%'));
        }

        if ($request->filled('pharmacist_id')) {
            $query->where('pharmacist_id', $request->pharmacist_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        return $query;
    }

    public function getMedicineQty(Medicine $medicine)
    {
        $totalBatchQty = MedicineBatch::where('medicine_id', $medicine->id)
            ->where('status', 1)
            ->where('quantity', '>', 0)
            ->sum('quantity');

        return response()->json([
            'available_qty' => $totalBatchQty ?: ($medicine->available_qty ?? 0),
        ]);
    }
}
