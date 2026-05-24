<?php
namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use App\Models\IpdPatient;
use App\Models\Ipd\MedicineOrder;
use App\Models\OpdPatient;
use App\Models\Patient;
use App\Models\Pharmacy\Medicine;
use App\Models\Pharmacy\MedicineBatch;
use App\Models\Pharmacy\PharmacyTransaction;
use App\Models\Pharmacy\PharmacyTransactionItem;
use App\Models\Prescription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PharmacyTransactionController extends Controller
{
    public function index(Request $request)
    {
        // Base query: all filters except transaction_type (used for tab counts)
        $baseQuery = PharmacyTransaction::query();
        $this->applyCommonFilters($baseQuery, $request);

        // Tab counts — always reflect current filters but not the active type tab
        $typeCounts = (clone $baseQuery)
            ->selectRaw('transaction_type, count(*) as cnt')
            ->groupBy('transaction_type')
            ->pluck('cnt', 'transaction_type');

        $tabAllCount = $typeCounts->sum();
        $tabOpdCount = (int) ($typeCounts->get('opd', 0));
        $tabIpdCount = (int) ($typeCounts->get('ipd', 0));
        $tabOtcCount = (int) ($typeCounts->get('otc', 0));

        // Actual rows — now also filter by type if a tab is active
        $query = PharmacyTransaction::with(['patient', 'pharmacist', 'opdPatient', 'ipdPatient'])->latest();
        $this->applyCommonFilters($query, $request);
        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        $transactions = $query->get();

        $today   = Carbon::today();
        $todayTx = PharmacyTransaction::whereDate('created_at', $today);

        // Today stats
        $todayTotal    = (clone $todayTx)->whereIn('status', ['completed', 'approved'])->sum('total_amount');
        $todayCount    = (clone $todayTx)->whereIn('status', ['completed', 'approved'])->count();
        $opdTodayTotal = (clone $todayTx)->where('transaction_type', 'opd')->whereIn('status', ['completed', 'approved'])->sum('total_amount');
        $opdTodayCount = (clone $todayTx)->where('transaction_type', 'opd')->whereIn('status', ['completed', 'approved'])->count();
        $ipdTodayCount = (clone $todayTx)->where('transaction_type', 'ipd')->count();
        $otcTotal      = (clone $todayTx)->where('transaction_type', 'otc')->where('status', 'completed')->sum('total_amount');
        $otcTodayCount = (clone $todayTx)->where('transaction_type', 'otc')->where('status', 'completed')->count();

        // Global stats
        $pendingCount   = PharmacyTransaction::where('status', 'pending')->count();
        $ipdToBeBilled  = PharmacyTransaction::where('transaction_type', 'ipd')->where('status', 'approved')->sum('total_amount');
        $unpaidOpdCount = PharmacyTransaction::where('transaction_type', 'opd')->where('payment_status', 'unpaid')->where('status', 'completed')->count();

        $pharmacists = User::whereIn('id', PharmacyTransaction::distinct()->pluck('pharmacist_id')->filter())
            ->orderBy('name')->get();

        return view('pharmacy.transactions.index', compact(
            'transactions', 'todayTotal', 'todayCount', 'pendingCount',
            'opdTodayTotal', 'opdTodayCount',
            'ipdTodayCount', 'ipdToBeBilled',
            'otcTotal', 'otcTodayCount',
            'unpaidOpdCount', 'pharmacists',
            'tabAllCount', 'tabOpdCount', 'tabIpdCount', 'tabOtcCount'
        ));
    }

    public function create()
    {
        $medicines = Medicine::where('status', 1)->orderBy('medicine_name')->get();
        $users     = User::orderBy('name')->get();
        $stores    = MedicineBatch::where('status', 1)->distinct()->pluck('store')->filter()->sort()->values();

        $opdPatients    = OpdPatient::with('patient')->orderByDesc('id')->get();
        $ipdPatients    = IpdPatient::with('patient')->where('status', 'Admitted')->get();
        $prescriptions  = Prescription::orderByDesc('id')->get();
        $medicineOrders = MedicineOrder::whereIn('ipd_id', $ipdPatients->pluck('id'))
            ->whereIn('status', ['pending', 'approved'])
            ->with(['medicine', 'prescribedBy'])
            ->get()
            ->map(fn($o) => [
                'id'            => $o->id,
                'ipd_id'        => $o->ipd_id,
                'medicine_id'   => $o->medicine_id,
                'medicine_name' => $o->medicine->medicine_name ?? '',
                'qty'           => (int) $o->qty,
                'prescribed_by' => $o->prescribedBy->name ?? '',
            ]);
        $beds = Bed::orderBy('name')->get();

        // Preview transaction numbers
        $nextOpdNo = 'OPDT-' . str_pad((PharmacyTransaction::where('transaction_type', 'opd')->max('id') ?? 0) + 1, 5, '0', STR_PAD_LEFT);
        $nextIpdNo = 'IpdT-' . str_pad((PharmacyTransaction::where('transaction_type', 'ipd')->max('id') ?? 0) + 1, 5, '0', STR_PAD_LEFT);
        $nextOtcNo = 'OTC-' . str_pad((PharmacyTransaction::where('transaction_type', 'otc')->max('id') ?? 0) + 1, 5, '0', STR_PAD_LEFT);

        return view('pharmacy.transactions.create', compact(
            'medicines', 'users', 'stores',
            'opdPatients', 'ipdPatients', 'prescriptions', 'medicineOrders', 'beds',
            'nextOpdNo', 'nextIpdNo', 'nextOtcNo'
        ));
    }

    public function store(Request $request)
    {
        $type = $request->transaction_type;

        $baseRules = [
            'transaction_type'     => 'required|in:opd,ipd,otc',
            'items'                => 'required|array|min:1',
            'items.*.medicine_id'  => 'required|exists:medicines,id',
            'items.*.qty_required' => 'required|integer|min:1',
            'items.*.store'        => 'required|string',
        ];

        $typeRules = match ($type) {
            'opd'   => [
                'opd_patient_id' => 'required|exists:opd_patients,id',
                'payment_status' => 'required|in:paid,unpaid,partial',
            ],
            'ipd'   => [
                'ipd_patient_id' => 'required|exists:i_p_d_patients,id',
                'ward_bed'       => 'required|string',
                'requisition_no' => 'required|string',
                'request_source' => 'required|string',
            ],
            'otc'   => [
                'payment_method' => 'required|in:cash,card,mobile_banking',
                'payment_status' => 'required|in:paid,unpaid,partial',
            ],
            default => [],
        };

        $validator = Validator::make($request->all(), array_merge($baseRules, $typeRules));

        if ($validator->fails()) {
            return redirect()->route('admin.pharmacy.transactions')
                ->withErrors($validator)
                ->withInput()
                ->with('show_create_modal', true);
        }

        DB::transaction(function () use ($request, $type) {
            $lastId = PharmacyTransaction::max('id') ?? 0;
            $prefix = match ($type) {
                'opd' => 'OPDT-',
                'ipd' => 'IpdT-',
                'otc' => 'OTC-',
            };
            $transactionNo = $prefix . str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);

            $data = [
                'transaction_no'   => $transactionNo,
                'transaction_type' => $type,
                'pharmacist_id'    => auth()->id(),
                'drug_count'       => count($request->items),
                'total_amount'     => 0,
                'discount_amount'  => (float) ($request->discount_amount ?? 0),
                'paid_amount'      => (float) ($request->paid_amount ?? 0),
                'note'             => $request->note ?: null,
                'status'           => $type === 'otc' ? 'completed' : 'pending',
            ];

            if ($type === 'opd') {
                $opdPatient              = OpdPatient::findOrFail($request->opd_patient_id);
                $data['opd_patient_id']  = $opdPatient->id;
                $data['patient_id']      = $opdPatient->patient_id;
                $data['prescription_id'] = $request->prescription_id ?: null;
                $data['payment_status']  = $request->payment_status;
            } elseif ($type === 'ipd') {
                $ipdPatient             = IpdPatient::findOrFail($request->ipd_patient_id);
                $data['ipd_patient_id'] = $ipdPatient->id;
                $data['patient_id']     = $ipdPatient->patient_id;
                $data['requisition_no'] = $request->requisition_no;
                $data['ward_bed']       = $request->ward_bed;
                $data['request_source'] = $request->request_source;
            } elseif ($type === 'otc') {
                $data['customer_name']  = $request->customer_name ?: null;
                $data['customer_phone'] = $request->customer_phone ?: null;
                $data['payment_method'] = $request->payment_method;
                $data['payment_status'] = $request->payment_status;
            }

            $transaction = PharmacyTransaction::create($data);
            $totalAmount = 0;

            foreach ($request->items as $item) {
                $medicine = Medicine::find($item['medicine_id']);

                $batch = MedicineBatch::where('medicine_id', $item['medicine_id'])
                    ->where('store', $item['store'])
                    ->where('quantity', '>', 0)
                    ->orderBy('expiry_date')
                    ->first();

                $unitPrice    = $batch ? (float) $batch->selling_price : 0;
                $qty          = (int) $item['qty_required'];
                $subtotal     = $unitPrice * $qty;
                $totalAmount += $subtotal;

                PharmacyTransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'medicine_id'    => $item['medicine_id'],
                    'batch_id'       => $batch?->id,
                    'dosage'         => $item['dosage'] ?? null,
                    'duration'       => $item['duration'] ?? null,
                    'qty_required'   => $qty,
                    'available_qty'  => $medicine->available_qty ?? 0,
                    'unit_price'     => $unitPrice,
                    'subtotal'       => $subtotal,
                    'store'          => $item['store'],
                ]);

                // Deduct stock immediately for OTC; defer for OPD/Ipd until approved
                if ($type === 'otc' && $batch) {
                    $batch->decrement('quantity', $qty);
                    $this->logStockMovement($medicine, $batch, $qty, $unitPrice, $transaction, 'pharmacy_dispense');
                }
            }

            $transaction->update(['total_amount' => $totalAmount]);

            // Mark source medicine orders as dispensed for Ipd transactions
            if ($type === 'ipd') {
                $orderIds = collect($request->items)
                    ->pluck('medicine_order_id')
                    ->filter()
                    ->map(fn($id) => (int) $id)
                    ->all();
                if ($orderIds) {
                    MedicineOrder::whereIn('id', $orderIds)->update(['status' => 'dispensed']);
                }
            }
        });

        return redirect()->route('admin.pharmacy.transactions')
            ->with('success', 'Transaction created successfully.');
    }

    public function show($id)
    {
        $transaction = PharmacyTransaction::with([
            'patient', 'pharmacist',
            'opdPatient.patient', 'ipdPatient.patient',
            'prescription', 'items.medicine',
            'returns.items.medicine',
        ])->findOrFail($id);

        return view('pharmacy.transactions.show', compact('transaction'));
    }

    public function approve($id)
    {
        $transaction = PharmacyTransaction::with('items')->findOrFail($id);

        if ($transaction->status !== 'pending') {
            return redirect()->route('admin.pharmacy.transactions')
                ->with('error', 'Only pending transactions can be approved.');
        }

        DB::transaction(function () use ($transaction) {
            // Deduct stock on approval for OPD / Ipd
            if (in_array($transaction->transaction_type, ['opd', 'ipd'])) {
                foreach ($transaction->items as $item) {
                    $batch = MedicineBatch::where('medicine_id', $item->medicine_id)
                        ->where('store', $item->store)
                        ->where('quantity', '>', 0)
                        ->orderBy('expiry_date')
                        ->first();

                    if ($batch) {
                        $batch->decrement('quantity', $item->qty_required);
                        $item->update(['batch_id' => $batch->id]);
                        $medicine = Medicine::find($item->medicine_id);
                        $this->logStockMovement($medicine, $batch, $item->qty_required, $item->unit_price, $transaction, 'pharmacy_dispense');
                    }
                }
            }

            $newStatus = $transaction->transaction_type === 'ipd' ? 'approved' : 'completed';
            $transaction->update(['status' => $newStatus]);
        });

        return redirect()->route('admin.pharmacy.transactions')
            ->with('success', "Transaction {$transaction->transaction_no} approved.");
    }

    public function getIpdMedicineOrders($ipdPatientId)
    {
        $orders = MedicineOrder::where('ipd_id', $ipdPatientId)
            ->whereIn('status', ['pending', 'approved'])
            ->with(['medicine', 'prescribedBy'])
            ->get();

        return response()->json($orders->map(fn($o) => [
            'id'            => $o->id,
            'medicine_id'   => $o->medicine_id,
            'medicine_name' => $o->medicine->medicine_name ?? '',
            'qty'           => (int) $o->qty,
            'status'        => $o->status,
            'prescribed_by' => $o->prescribedBy->name ?? '',
        ]));
    }

    public function getPrescriptionMedicines($id)
    {
        $prescription = Prescription::with('medicines.medicine')->findOrFail($id);

        return response()->json([
            'prescription_no' => $prescription->prescription_no,
            'medicines'       => $prescription->medicines->map(fn($pm) => [
                'medicine_id'   => $pm->medicine_id,
                'medicine_name' => $pm->medicine->medicine_name ?? '',
                'dosage'        => trim(($pm->dosage ?? '') . ($pm->frequency ? ' ' . $pm->frequency : '')),
                'duration'      => $pm->duration ?? '',
            ]),
        ]);
    }

    public function getMedicineQty(Medicine $medicine, Request $request)
    {
        $store = $request->query('store');

        // Scoped to the selected store when provided, otherwise any active batch
        $batchQuery = MedicineBatch::where('medicine_id', $medicine->id)
            ->where('status', 1)
            ->where('quantity', '>', 0);

        if ($store) {
            $batchQuery->where('store', $store);
        }

        $batch    = (clone $batchQuery)->orderBy('expiry_date')->first();
        $totalQty = (clone $batchQuery)->sum('quantity');

        return response()->json([
            'available_qty' => (int) ($totalQty ?: ($medicine->available_qty ?? 0)),
            'unit_price'    => $batch ? (float) $batch->selling_price : 0,
            'store_found'   => (bool) $batch,
        ]);
    }

    public function export(Request $request)
    {
        $transactions = $this->buildFilteredQuery($request)
            ->with(['patient', 'pharmacist', 'opdPatient', 'ipdPatient'])
            ->get();

        $filename = 'pharmacy-transactions-' . now()->format('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($transactions) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Txn No', 'Type', 'Date', 'Patient / Customer',
                'Drug Count', 'Total (TK)', 'Discount (TK)', 'Paid (TK)',
                'Payment Method', 'Payment Status', 'Status', 'Pharmacist', 'Note',
            ]);

            foreach ($transactions as $t) {
                $name = $t->transaction_type === 'otc'
                    ? ($t->customer_name ?? 'Walk-in')
                    : ($t->patient->patient_name ?? '');

                fputcsv($handle, [
                    $t->transaction_no,
                    strtoupper($t->transaction_type),
                    $t->created_at->format('d/m/Y'),
                    $name,
                    $t->drug_count,
                    number_format($t->total_amount, 2),
                    number_format($t->discount_amount, 2),
                    number_format($t->paid_amount, 2),
                    $t->payment_method ? ucfirst(str_replace('_', ' ', $t->payment_method)) : '',
                    $t->payment_status ?? '',
                    $t->status,
                    $t->pharmacist->name ?? '',
                    $t->note ?? '',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function print(Request $request)
    {
        $transactions = $this->buildFilteredQuery($request)
            ->with(['patient', 'pharmacist', 'opdPatient', 'ipdPatient', 'items.medicine'])
            ->get();

        $filters = $request->only([
            'transaction_type', 'date_from', 'date_to',
            'transaction_no', 'patient_name', 'status', 'payment_status', 'pharmacist_id',
        ]);

        return view('pharmacy.transactions.print', compact('transactions', 'filters'));
    }

    private function applyCommonFilters($query, Request $request): void
    {
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('transaction_no')) {
            $query->where('transaction_no', 'like', '%' . $request->transaction_no . '%');
        }
        if ($request->filled('patient_name')) {
            $query->whereHas('patient', fn($q) => $q->where('patient_name', 'like', '%' . $request->patient_name . '%'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->filled('pharmacist_id')) {
            $query->where('pharmacist_id', $request->pharmacist_id);
        }
    }

    private function buildFilteredQuery(Request $request)
    {
        $query = PharmacyTransaction::latest();

        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('transaction_no')) {
            $query->where('transaction_no', 'like', '%' . $request->transaction_no . '%');
        }
        if ($request->filled('patient_name')) {
            $query->whereHas('patient', fn($q) => $q->where('patient_name', 'like', '%' . $request->patient_name . '%'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->filled('pharmacist_id')) {
            $query->where('pharmacist_id', $request->pharmacist_id);
        }

        return $query;
    }

    /**
     * Mirror a pharmacy batch deduction into the canonical inventory layer
     * so that inventory_items.liveBalance() stays in sync with batch quantities.
     */
    private function logStockMovement($medicine, $batch, int $qty, float $unitPrice, $transaction, string $reason = 'pharmacy_dispense'): void
    {
        if (! $medicine || ! $medicine->inventory_item_id) return;

        $whId = \DB::table('inventory_warehouses')
            ->where('is_active', 1)
            ->where(function ($q) use ($batch) {
                $q->where('name', $batch?->store ?? 'Main Pharmacy')
                  ->orWhere('type', 'pharmacy');
            })
            ->value('id') ?? \DB::table('inventory_warehouses')->where('is_active', 1)->value('id');
        if (! $whId) return;

        // Running balance after this OUT movement
        $prevBal = (float) \DB::table('stock_movements')
            ->where('inventory_item_id', $medicine->inventory_item_id)
            ->selectRaw("COALESCE(SUM(CASE WHEN direction='in' THEN quantity ELSE -quantity END), 0) as bal")
            ->value('bal');

        \DB::table('stock_movements')->insert([
            'inventory_item_id' => $medicine->inventory_item_id,
            'warehouse_id'      => $whId,
            'direction'         => 'out',
            'quantity'          => $qty,
            'unit_cost'         => $unitPrice,
            'balance_after'     => $prevBal - $qty,
            'reason'            => $reason,
            'source_type'       => \App\Models\Pharmacy\PharmacyTransaction::class,
            'source_id'         => $transaction->id,
            'reference_no'      => $transaction->transaction_no,
            'remarks'           => 'Pharmacy ' . strtoupper($transaction->transaction_type) . ' dispensing',
            'performed_by'      => auth()->id(),
            'performed_at'      => now(),
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
    }
}
