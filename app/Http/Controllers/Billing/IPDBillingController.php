<?php
namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Charges\Charge;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\IpdPatient;
use App\Models\Transaction;
use Illuminate\Http\Request;

class IpdBillingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ipdPatients = IpdPatient::with([
            'patient',
            'doctor',
            'department',
            'bedAllocations' => fn($q) => $q->latest('id'),
            'bedAllocations.bed',
        ])
            ->latest()
            ->get();

        $ipdTransactions = Transaction::with([
            'patient',
            'ipdPatient.doctor',
        ])
            ->whereRaw('LOWER(section) = ?', ['ipd'])
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->get();

        return view('billing.ipd_billing.index', compact('ipdPatients', 'ipdTransactions'));
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $ipdPatient = IpdPatient::with([
            'patient',
            'doctor',
            'department',
            'bedAllocations' => fn($q) => $q->latest('id'),
            'bedAllocations.bed',
            'charges'        => fn($q)        => $q->orderBy('date', 'desc'),
            'charges.charge.chargeCategory',
            'charges.charge.uniteType',
            'transactions'   => fn($q)   => $q->orderBy('payment_date', 'desc'),
        ])->findOrFail($id);

        $doctors          = Doctor::select('id', 'name')->get();
        $departments      = Department::select('id', 'name')->get();
        $availableCharges = Charge::with(['chargeCategory'])->get();

        return view('billing.ipd_billing.show', compact('ipdPatient', 'doctors', 'departments', 'availableCharges'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
