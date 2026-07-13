<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy\Medicine;
use App\Models\Pharmacy\MedicineBatch;
use App\Models\Pharmacy\MedicineCategory;
use App\Models\Pharmacy\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $batches = $this->buildFilteredQuery($request)->latest()->get();

        // Stats
        $now = Carbon::today();

        $totalDrugs = Medicine::count();
        $totalStockValue = MedicineBatch::where('status', 1)->selectRaw('SUM(quantity * purchase_price) as total')->value('total') ?? 0;
        $lowStockCount = MedicineBatch::where('status', 1)
            ->where('quantity', '>', 0)
            ->whereHas('medicine', fn($q) => $q->whereColumn('medicine_batches.quantity', '<=', DB::raw('CAST(medicines.reorder_level AS UNSIGNED)')))
            ->count();
        $nearExpiryCount = MedicineBatch::where('status', 1)->whereBetween('expiry_date', [$now, $now->copy()->addDays(90)])->count();
        $expiredCount = MedicineBatch::where('status', 1)->where('expiry_date', '<', $now)->count();
        $outOfStockCount = MedicineBatch::where('status', 1)->where('quantity', '<=', 0)->count();

        // Filter options
        $categories = MedicineCategory::orderBy('name')->get();
        $stores = MedicineBatch::where('status', 1)->distinct()->pluck('store')->filter()->sort()->values();

        return view('pharmacy.inventory.index', compact(
            'batches',
            'totalDrugs',
            'totalStockValue',
            'lowStockCount',
            'nearExpiryCount',
            'expiredCount',
            'outOfStockCount',
            'categories',
            'stores'
        ));
    }

    public function createPurchase()
    {
        $medicines = Medicine::where('status', 1)->orderBy('medicine_name')->get()
            ->map(fn($m) => ['id' => $m->id, 'name' => $m->medicine_name]);

        $stores    = MedicineBatch::where('status', 1)->distinct()->pluck('store')->filter()->sort()->values();
        $suppliers = Supplier::orderBy('supplier_name')->get();

        return view('pharmacy.inventory.purchase-form', compact('medicines', 'stores', 'suppliers'));
    }

    public function storePurchase(Request $request)
    {
        $request->validate([
            'supplier_id'              => 'nullable|exists:suppliers,id',
            'purchase_date'            => 'nullable|date',
            'invoice_no'               => 'nullable|string|max:100',
            'note'                     => 'nullable|string|max:500',
            'items'                    => 'required|array|min:1',
            'items.*.medicine_id'      => 'required|exists:medicines,id',
            'items.*.batch_no'         => 'required|string|max:100',
            'items.*.store'            => 'required|string|max:100',
            'items.*.quantity'         => 'required|integer|min:1',
            'items.*.purchase_price'   => 'required|numeric|min:0',
            'items.*.selling_price'    => 'required|numeric|min:0',
            'items.*.expiry_date'      => 'nullable|date',
        ]);

        $note      = $request->note ?: null;
        $invoiceNo = $request->invoice_no ?: null;
        $batchNote = $invoiceNo ? trim("Invoice: {$invoiceNo}" . ($note ? " | {$note}" : '')) : $note;

        foreach ($request->items as $item) {
            $existing = MedicineBatch::where('medicine_id', $item['medicine_id'])
                ->where('batch_no', $item['batch_no'])
                ->where('store', $item['store'])
                ->first();

            if ($existing) {
                $existing->increment('quantity', $item['quantity']);
                $existing->update([
                    'purchase_price' => $item['purchase_price'],
                    'selling_price'  => $item['selling_price'],
                ]);
            } else {
                MedicineBatch::create([
                    'medicine_id'    => $item['medicine_id'],
                    'batch_no'       => $item['batch_no'],
                    'store'          => $item['store'],
                    'quantity'       => $item['quantity'],
                    'purchase_price' => $item['purchase_price'],
                    'selling_price'  => $item['selling_price'],
                    'expiry_date'    => $item['expiry_date'] ?: null,
                    'note'           => $batchNote,
                    'status'         => 1,
                ]);
            }
        }

        return redirect()->route('admin.pharmacy.inventory')
            ->with('success', 'Purchase recorded. Stock updated successfully.');
    }

    public function show($id)
    {
        $batch = MedicineBatch::with(['medicine.category', 'medicine.medicalGroup', 'medicine.unit', 'medicine.company'])
            ->findOrFail($id);

        return view('pharmacy.inventory.show', compact('batch'));
    }

    public function export(Request $request)
    {
        $batches = $this->buildFilteredQuery($request)->latest()->get();

        $filename = 'inventory_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($batches) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Drug Name', 'Category', 'Batch No', 'Manufacture Date', 'Expiry Date', 'Store', 'Qty Available', 'Reorder Level', 'Purchase Price', 'Selling Price', 'Stock Value', 'Status']);

            $now = Carbon::today();
            foreach ($batches as $batch) {
                $medicine     = $batch->medicine;
                $reorderLevel = (int) ($medicine->reorder_level ?? 0);
                $isExpired    = $batch->expiry_date && $batch->expiry_date->isPast();
                $isNearExpiry = $batch->expiry_date && !$isExpired && $batch->expiry_date->diffInDays($now) <= 90;
                $isOutOfStock = $batch->quantity <= 0;
                $isLowStock   = !$isOutOfStock && $reorderLevel > 0 && $batch->quantity <= $reorderLevel;

                if ($isExpired)       $status = 'Expired';
                elseif ($isOutOfStock) $status = 'Out of Stock';
                elseif ($isNearExpiry) $status = 'Near Expiry';
                elseif ($isLowStock)   $status = 'Low Stock';
                else                   $status = 'Normal';

                fputcsv($handle, [
                    $medicine->medicine_name ?? '—',
                    $medicine->category->name ?? '—',
                    $batch->batch_no,
                    $batch->manufacture_date?->format('d/m/Y') ?? '—',
                    $batch->expiry_date?->format('d/m/Y') ?? '—',
                    $batch->store,
                    $batch->quantity,
                    $reorderLevel ?: '—',
                    number_format($batch->purchase_price, 2),
                    number_format($batch->selling_price, 2),
                    number_format($batch->quantity * $batch->purchase_price, 2),
                    $status,
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }

    public function print(Request $request)
    {
        $batches = $this->buildFilteredQuery($request)->latest()->get();

        $filters = $request->only(['medicine_name', 'batch_no', 'store', 'category_id', 'stock_status', 'expiry_status']);

        return view('pharmacy.inventory.print', compact('batches', 'filters'));
    }

    private function buildFilteredQuery(Request $request)
    {
        $query = MedicineBatch::with(['medicine.category', 'medicine.medicalGroup', 'medicine.unit'])
            ->where('status', 1);

        if ($request->filled('medicine_name')) {
            $query->whereHas('medicine', fn($q) => $q->where('medicine_name', 'like', '%' . $request->medicine_name . '%'));
        }

        if ($request->filled('batch_no')) {
            $query->where('batch_no', 'like', '%' . $request->batch_no . '%');
        }

        if ($request->filled('store')) {
            $query->where('store', $request->store);
        }

        if ($request->filled('category_id')) {
            $query->whereHas('medicine', fn($q) => $q->where('medicine_category_id', $request->category_id));
        }

        if ($request->filled('stock_status')) {
            match ($request->stock_status) {
                'out'  => $query->where('quantity', '<=', 0),
                'low'  => $query->where('quantity', '>', 0)
                    ->whereHas('medicine', fn($q) => $q->whereColumn('medicine_batches.quantity', '<=', DB::raw('CAST(medicines.reorder_level AS UNSIGNED)'))),
                default => null,
            };
        }

        if ($request->filled('expiry_status')) {
            $now = Carbon::today();
            match ($request->expiry_status) {
                'expired' => $query->where('expiry_date', '<', $now),
                'near'    => $query->whereBetween('expiry_date', [$now, $now->copy()->addDays(90)]),
                'valid'   => $query->where('expiry_date', '>', $now->copy()->addDays(90)),
                default   => null,
            };
        }

        return $query;
    }
}
