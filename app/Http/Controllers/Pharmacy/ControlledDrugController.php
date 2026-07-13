<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy\ControlledDrug;
use App\Models\Pharmacy\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ControlledDrugController extends Controller
{
    public function index(Request $request)
    {
        $query = ControlledDrug::with(['medicine', 'createdBy']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('doctor_name', 'like', "%{$search}%")
                  ->orWhere('generic_name', 'like', "%{$search}%")
                  ->orWhere('dea_number', 'like', "%{$search}%")
                  ->orWhere('lot_number', 'like', "%{$search}%")
                  ->orWhere('ndc_code', 'like', "%{$search}%")
                  ->orWhere('entry_no', 'like', "%{$search}%");
            });
        }

        if ($request->filled('schedule')) {
            $query->where('schedule', $request->schedule);
        }

        if ($request->filled('action_type')) {
            $query->where('action_type', $request->action_type);
        }

        if ($request->filled('inventory_status')) {
            $query->where('inventory_status', $request->inventory_status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('entry_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('entry_date', '<=', $request->date_to);
        }

        $entries = $query->latest('entry_date')->paginate(15)->withQueryString();

        // Stats
        $totalCount    = ControlledDrug::count();
        $activeCount   = ControlledDrug::where('inventory_status', 'available')->count();
        $scheduleIIVCount = ControlledDrug::whereIn('schedule', ['Schedule II', 'Schedule III', 'Schedule IV', 'Schedule V'])->count();

        // Medicines for the create form
        $medicines = Medicine::orderBy('medicine_name')->get();

        return view('pharmacy.controlled-drugs.index', compact(
            'entries',
            'totalCount',
            'activeCount',
            'scheduleIIVCount',
            'medicines'
        ));
    }

    public function create()
    {
        $medicines = Medicine::orderBy('medicine_name')->get();

        return view('pharmacy.controlled-drugs.create', compact('medicines'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'entry_date'       => 'required|date',
            'doctor_name'      => 'required|string|max:255',
            'dea_number'       => 'nullable|string|max:100',
            'generic_name'     => 'required|string|max:255',
            'lot_number'       => 'required|string|max:100',
            'schedule'         => 'required|string|max:50',
            'expiration_date'  => 'nullable|date',
            'ndc_code'         => 'nullable|string|max:100',
            'action_type'      => 'required|in:received,removed',
            'quantity'         => 'required|numeric|min:0.01',
            'unit'             => 'required|string|max:20',
            'inventory_status' => 'required|in:available,low_stock,out_of_stock',
            'notes'            => 'nullable|string',
        ]);

        $entry_no = 'CD-' . strtoupper(uniqid());

        ControlledDrug::create([
            'entry_no'         => $entry_no,
            'entry_date'       => $request->entry_date,
            'doctor_name'      => $request->doctor_name,
            'dea_number'       => $request->dea_number,
            'medicine_id'      => $request->medicine_id ?: null,
            'generic_name'     => $request->generic_name,
            'lot_number'       => $request->lot_number,
            'schedule'         => $request->schedule,
            'expiration_date'  => $request->expiration_date,
            'ndc_code'         => $request->ndc_code,
            'action_type'      => $request->action_type,
            'quantity'         => $request->quantity,
            'unit'             => $request->unit,
            'inventory_status' => $request->inventory_status,
            'notes'            => $request->notes,
            'created_by'       => Auth::id(),
        ]);

        return back()->with('success', "Controlled drug entry {$entry_no} added successfully.");
    }

    public function show($id)
    {
        $entry = ControlledDrug::with(['medicine', 'createdBy'])->findOrFail($id);

        return view('pharmacy.controlled-drugs.show', compact('entry'));
    }

    public function export(Request $request)
    {
        $query = ControlledDrug::with(['medicine', 'createdBy']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('doctor_name', 'like', "%{$search}%")
                  ->orWhere('generic_name', 'like', "%{$search}%")
                  ->orWhere('dea_number', 'like', "%{$search}%")
                  ->orWhere('lot_number', 'like', "%{$search}%");
            });
        }
        if ($request->filled('schedule'))         $query->where('schedule', $request->schedule);
        if ($request->filled('action_type'))      $query->where('action_type', $request->action_type);
        if ($request->filled('inventory_status')) $query->where('inventory_status', $request->inventory_status);
        if ($request->filled('date_from'))        $query->whereDate('entry_date', '>=', $request->date_from);
        if ($request->filled('date_to'))          $query->whereDate('entry_date', '<=', $request->date_to);

        $entries  = $query->latest('entry_date')->get();
        $filename = 'controlled_drugs_' . now()->format('Ymd_His') . '.csv';

        return response()->stream(function () use ($entries) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Entry No', 'Entry Date', 'Dr. Name', 'DEA No', 'Generic Name', 'Lot Number', 'Schedule', 'Expiry Date', 'NDC Code', 'Action', 'Qty', 'Unit', 'Status', 'Notes', 'Added By']);

            foreach ($entries as $e) {
                fputcsv($handle, [
                    $e->entry_no,
                    $e->entry_date->format('d/m/Y H:i'),
                    $e->doctor_name,
                    $e->dea_number ?? '—',
                    $e->generic_name,
                    $e->lot_number,
                    $e->schedule,
                    $e->expiration_date?->format('d/m/Y') ?? '—',
                    $e->ndc_code ?? '—',
                    ucfirst($e->action_type),
                    $e->quantity . ' ' . $e->unit,
                    $e->unit,
                    ucwords(str_replace('_', ' ', $e->inventory_status)),
                    $e->notes ?? '',
                    $e->createdBy->name ?? '—',
                ]);
            }
            fclose($handle);
        }, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
