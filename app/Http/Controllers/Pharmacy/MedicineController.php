<?php
namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy\Company;
use App\Models\Pharmacy\IpdIssue;
use App\Models\Pharmacy\MedicalGroup;
use App\Models\Pharmacy\Medicine;
use App\Models\Pharmacy\MedicineBatch;
use App\Models\Pharmacy\MedicineCategory;
use App\Models\Pharmacy\MedicineUnit;
use App\Models\Pharmacy\OpdDispense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MedicineController extends Controller
{
    public function index()
    {
        $medicines = Medicine::with(['category', 'company', 'medicalGroup', 'unit'])
            ->latest()
            ->get();

        // Stats
        $totalOpdSales = OpdDispense::where('status', 'completed')->sum('total_amount');
        $totalIpdSales = IpdIssue::where('status', 'completed')->sum('total_amount');
        $totalSales    = $totalOpdSales + $totalIpdSales;

        $totalPurchases = MedicineBatch::selectRaw('SUM(purchase_price * quantity) as total')->value('total') ?? 0;
        $totalRevenue   = $totalSales - $totalPurchases;

        $totalOpdPending = OpdDispense::where('status', 'pending')->sum('total_amount');
        $totalIpdPending = IpdIssue::where('status', 'pending')->sum('total_amount');

        // Build unified transactions list
        $opdDispenses = OpdDispense::with('patient')
            ->latest()
            ->get()
            ->map(function ($d) {
                return [
                    'date'       => $d->created_at,
                    'type'       => 'OPD SALE',
                    'ref'        => $d->dispense_no,
                    'patient'    => $d->patient->name ?? '—',
                    'drug_count' => $d->drug_count,
                    'amount'     => $d->total_amount,
                    'department' => 'OPD Pharmacy',
                    'status'     => $d->status,
                ];
            });

        $ipdIssues = IpdIssue::with('patient')
            ->latest()
            ->get()
            ->map(function ($i) {
                return [
                    'date'       => $i->created_at,
                    'type'       => 'Ipd ISSUE',
                    'ref'        => $i->issue_no,
                    'patient'    => $i->patient->name ?? '—',
                    'drug_count' => $i->drug_count,
                    'amount'     => $i->total_amount,
                    'department' => $i->request_source ?? 'Ipd Pharmacy',
                    'status'     => $i->status,
                ];
            });

        $purchases = MedicineBatch::with('medicine')
            ->latest()
            ->get()
            ->map(function ($b) {
                return [
                    'date'       => $b->created_at,
                    'type'       => 'PURCHASE',
                    'ref'        => $b->batch_no,
                    'patient'    => '—',
                    'drug_count' => $b->quantity,
                    'amount'     => $b->purchase_price * $b->quantity,
                    'department' => $b->store ?? 'Main Pharmacy',
                    'status'     => $b->status ? 'completed' : 'cancelled',
                ];
            });

        $transactions = $opdDispenses->concat($ipdIssues)->concat($purchases)
            ->sortByDesc('date')
            ->values();

        return view('pharmacy.medicine.index', compact(
            'medicines',
            'totalSales',
            'totalRevenue',
            'totalPurchases',
            'totalOpdPending',
            'totalIpdPending',
            'transactions'
        ));
    }

    public function create()
    {
        $medicine_categories = MedicineCategory::orderBy('name')->get();
        $companies           = Company::orderBy('name')->get();
        $medical_groups      = MedicalGroup::orderBy('name')->get();
        $medicine_units      = MedicineUnit::orderBy('name')->get();

        return view('pharmacy.medicine.create', compact(
            'medicine_categories',
            'companies',
            'medical_groups',
            'medicine_units'
        ));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'medicine_name'        => 'required|string|max:255',
            'medicine_category_id' => 'required|exists:medicine_categories,id',
            'company_id'           => 'nullable|exists:companies,id',
            'medical_group_id'     => 'nullable|exists:medical_groups,id',
            'medicine_unit_id'     => 'required|exists:medicine_units,id',
            'medicine_composition' => 'nullable|string|max:255',
            'min_level'            => 'nullable|string|max:100',
            'reorder_level'        => 'nullable|string|max:100',
            'tax'                  => 'nullable|numeric|min:0',
            'box_packing'          => 'required|string|max:255',
            'vat_ac'               => 'nullable|string|max:255',
            'rack_number'          => 'nullable|string|max:255',
            'note'                 => 'nullable|string',
            'available_qty'        => 'nullable|integer|min:0',
            'photo'                => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.medicines.index')
                ->withErrors($validator)
                ->withInput()
                ->with('modal_type', 'create');
        }

        $photoName = null;

        if ($request->hasFile('photo')) {
            $photo     = $request->file('photo');
            $photoName = time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
            $photo->move(public_path('uploads/pharmacy/medicines'), $photoName);
        }

        Medicine::create([
            'medicine_name'        => $request->medicine_name,
            'medicine_category_id' => $request->medicine_category_id,
            'company_id'           => $request->company_id,
            'medical_group_id'     => $request->medical_group_id,
            'medicine_unit_id'     => $request->medicine_unit_id,
            'medicine_composition' => $request->medicine_composition,
            'min_level'            => $request->min_level,
            'reorder_level'        => $request->reorder_level,
            'tax'                  => $request->tax ?? 0,
            'box_packing'          => $request->box_packing,
            'vat_ac'               => $request->vat_ac,
            'rack_number'          => $request->rack_number,
            'note'                 => $request->note,
            'available_qty'        => $request->available_qty ?? 0,
            'photo'                => $photoName,
            'status'               => $request->has('status') ? 1 : 0,
        ]);

        return redirect()->route('admin.pharmacy.drug-master')
            ->with('success', 'Medicine created successfully.');
    }

    public function edit($id)
    {
        $medicine            = Medicine::findOrFail($id);
        $medicine_categories = MedicineCategory::orderBy('name')->get();
        $companies           = Company::orderBy('name')->get();
        $medical_groups      = MedicalGroup::orderBy('name')->get();
        $medicine_units      = MedicineUnit::orderBy('name')->get();

        return view('pharmacy.medicine.edit', compact(
            'medicine',
            'medicine_categories',
            'companies',
            'medical_groups',
            'medicine_units'
        ));
    }

    public function update(Request $request, $id)
    {
        $medicine = Medicine::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'medicine_name'        => 'required|string|max:255',
            'medicine_category_id' => 'required|exists:medicine_categories,id',
            'company_id'           => 'nullable|exists:companies,id',
            'medical_group_id'     => 'nullable|exists:medical_groups,id',
            'medicine_unit_id'     => 'required|exists:medicine_units,id',
            'medicine_composition' => 'nullable|string|max:255',
            'min_level'            => 'nullable|string|max:100',
            'reorder_level'        => 'nullable|string|max:100',
            'tax'                  => 'nullable|numeric|min:0',
            'box_packing'          => 'required|string|max:255',
            'vat_ac'               => 'nullable|string|max:255',
            'rack_number'          => 'nullable|string|max:255',
            'note'                 => 'nullable|string',
            'available_qty'        => 'nullable|integer|min:0',
            'photo'                => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.pharmacy.drug-master')
                ->withErrors($validator)
                ->withInput()
                ->with('modal_type', 'edit')
                ->with('edit_id', $medicine->id);
        }

        $photoName = $medicine->photo;

        if ($request->hasFile('photo')) {
            if ($medicine->photo && file_exists(public_path('uploads/pharmacy/medicines/' . $medicine->photo))) {
                @unlink(public_path('uploads/pharmacy/medicines/' . $medicine->photo));
            }

            $photo     = $request->file('photo');
            $photoName = time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
            $photo->move(public_path('uploads/pharmacy/medicines'), $photoName);
        }

        $medicine->update([
            'medicine_name'        => $request->medicine_name,
            'medicine_category_id' => $request->medicine_category_id,
            'company_id'           => $request->company_id,
            'medical_group_id'     => $request->medical_group_id,
            'medicine_unit_id'     => $request->medicine_unit_id,
            'medicine_composition' => $request->medicine_composition,
            'min_level'            => $request->min_level,
            'reorder_level'        => $request->reorder_level,
            'tax'                  => $request->tax ?? 0,
            'box_packing'          => $request->box_packing,
            'vat_ac'               => $request->vat_ac,
            'rack_number'          => $request->rack_number,
            'note'                 => $request->note,
            'available_qty'        => $request->available_qty ?? 0,
            'photo'                => $photoName,
            'status'               => $request->has('status') ? 1 : 0,
        ]);

        return redirect()->route('admin.pharmacy.drug-master')
            ->with('success', 'Medicine updated successfully.');
    }

    public function destroy($id)
    {
        $medicine = Medicine::findOrFail($id);

        if ($medicine->photo && file_exists(public_path('uploads/pharmacy/medicines/' . $medicine->photo))) {
            @unlink(public_path('uploads/pharmacy/medicines/' . $medicine->photo));
        }

        $medicine->delete();

        return redirect()->route('admin.pharmacy.drug-master')
            ->with('success', 'Medicine deleted successfully.');
    }

}
