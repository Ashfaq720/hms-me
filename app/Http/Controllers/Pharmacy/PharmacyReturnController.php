<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy\MedicineBatch;
use App\Models\Pharmacy\PharmacyReturn;
use App\Models\Pharmacy\PharmacyReturnItem;
use App\Models\Pharmacy\PharmacyTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PharmacyReturnController extends Controller
{
    public function index(Request $request)
    {
        $query = PharmacyReturn::with(['transaction', 'patient', 'returnedBy'])->latest();

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('return_no')) {
            $query->where('return_no', 'like', '%' . $request->return_no . '%');
        }
        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $returns = $query->get();

        $today       = Carbon::today();
        $todayRtn    = PharmacyReturn::whereDate('created_at', $today);
        $todayTotal  = (clone $todayRtn)->where('status', 'completed')->sum('total_amount');
        $todayCount  = (clone $todayRtn)->where('status', 'completed')->count();
        $pendingCount = PharmacyReturn::where('status', 'pending')->count();

        return view('pharmacy.returns.index', compact(
            'returns', 'todayTotal', 'todayCount', 'pendingCount'
        ));
    }

    public function create(Request $request)
    {
        // Transaction lookup by number for return form
        $transaction = null;
        if ($request->filled('txn')) {
            $transaction = PharmacyTransaction::with(['items.medicine', 'patient', 'opdPatient', 'ipdPatient'])
                ->where('transaction_no', $request->txn)
                ->whereIn('status', ['completed', 'approved'])
                ->first();
        }

        return view('pharmacy.returns.create', compact('transaction'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transaction_id'            => 'required|exists:pharmacy_transactions,id',
            'reason'                    => 'required|string|max:255',
            'items'                     => 'required|array|min:1',
            'items.*.transaction_item_id' => 'required|exists:pharmacy_transaction_items,id',
            'items.*.qty_returned'      => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $transaction = PharmacyTransaction::with('items')->findOrFail($request->transaction_id);

        // Validate qty_returned does not exceed qty_required minus already returned
        foreach ($request->items as $itemData) {
            $txItem = $transaction->items->find($itemData['transaction_item_id']);
            if (!$txItem) continue;

            $alreadyReturned = PharmacyReturnItem::where('transaction_item_id', $txItem->id)->sum('qty_returned');
            $maxReturnable   = $txItem->qty_required - $alreadyReturned;

            if ((int) $itemData['qty_returned'] > $maxReturnable) {
                return redirect()->back()
                    ->with('error', "Return qty for {$txItem->medicine->medicine_name} exceeds dispensed qty.")
                    ->withInput();
            }
        }

        DB::transaction(function () use ($request, $transaction) {
            $lastId   = PharmacyReturn::max('id') ?? 0;
            $returnNo = 'RTN-' . str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);

            $return = PharmacyReturn::create([
                'return_no'        => $returnNo,
                'transaction_id'   => $transaction->id,
                'transaction_type' => $transaction->transaction_type,
                'patient_id'       => $transaction->patient_id,
                'returned_by'      => auth()->id(),
                'total_amount'     => 0,
                'reason'           => $request->reason,
                'status'           => 'pending',
                'note'             => $request->note ?: null,
            ]);

            $totalAmount = 0;

            foreach ($request->items as $itemData) {
                $txItem    = $transaction->items->find($itemData['transaction_item_id']);
                $qtyReturn = (int) $itemData['qty_returned'];
                $subtotal  = (float) $txItem->unit_price * $qtyReturn;
                $totalAmount += $subtotal;

                PharmacyReturnItem::create([
                    'return_id'           => $return->id,
                    'transaction_item_id' => $txItem->id,
                    'medicine_id'         => $txItem->medicine_id,
                    'qty_returned'        => $qtyReturn,
                    'unit_price'          => $txItem->unit_price,
                    'subtotal'            => $subtotal,
                    'store'               => $txItem->store,
                ]);
            }

            $return->update(['total_amount' => $totalAmount]);
        });

        return redirect()->route('admin.pharmacy.returns')
            ->with('success', 'Return request created. Awaiting approval.');
    }

    public function show($id)
    {
        $return = PharmacyReturn::with([
            'transaction', 'patient', 'returnedBy',
            'items.medicine', 'items.transactionItem',
        ])->findOrFail($id);

        return view('pharmacy.returns.show', compact('return'));
    }

    public function approve($id)
    {
        $return = PharmacyReturn::with('items')->findOrFail($id);

        if ($return->status !== 'pending') {
            return redirect()->route('admin.pharmacy.returns')
                ->with('error', 'Only pending returns can be approved.');
        }

        DB::transaction(function () use ($return) {
            // Restore stock to batches
            foreach ($return->items as $item) {
                $batch = MedicineBatch::where('medicine_id', $item->medicine_id)
                    ->where('store', $item->store)
                    ->orderByDesc('id')
                    ->first();

                if ($batch) {
                    $batch->increment('quantity', $item->qty_returned);
                }
            }

            $return->update(['status' => 'completed']);
        });

        return redirect()->route('admin.pharmacy.returns')
            ->with('success', "Return {$return->return_no} approved and stock restored.");
    }

    public function getTransactionItems(Request $request)
    {
        $transaction = PharmacyTransaction::with(['items.medicine', 'patient', 'opdPatient', 'ipdPatient'])
            ->where('transaction_no', $request->transaction_no)
            ->whereIn('status', ['completed', 'approved'])
            ->first();

        if (!$transaction) {
            return response()->json(['error' => 'Transaction not found or not eligible for return.'], 404);
        }

        $items = $transaction->items->map(function ($item) {
            $alreadyReturned = PharmacyReturnItem::where('transaction_item_id', $item->id)->sum('qty_returned');
            return [
                'id'               => $item->id,
                'medicine_name'    => $item->medicine->medicine_name,
                'qty_required'     => $item->qty_required,
                'already_returned' => $alreadyReturned,
                'max_returnable'   => $item->qty_required - $alreadyReturned,
                'unit_price'       => (float) $item->unit_price,
                'store'            => $item->store,
            ];
        })->filter(fn($i) => $i['max_returnable'] > 0)->values();

        $patientName = $transaction->transaction_type === 'otc'
            ? ($transaction->customer_name ?? 'Walk-in')
            : ($transaction->patient->patient_name ?? '');

        return response()->json([
            'transaction_id'   => $transaction->id,
            'transaction_no'   => $transaction->transaction_no,
            'transaction_type' => $transaction->transaction_type,
            'patient_name'     => $patientName,
            'items'            => $items,
        ]);
    }
}
