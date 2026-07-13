<?php
namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Charges\Charge;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\OpdPatient;
use App\Models\Transaction;
use Illuminate\Http\Request;

class OPDBillingController extends Controller
{
    public function index()
    {
        $opdPatients = OpdPatient::with([
            'patient',
            'doctor',
            'department',
        ])
            ->latest()
            ->get();

        $opdTransactions = Transaction::with([
            'patient',
            'opdPatient.doctor',
        ])
            ->whereRaw('LOWER(section) = ?', ['opd'])
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->get();

        return view('billing.opd_billing.index', compact('opdPatients', 'opdTransactions'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show(string $id)
    {
        $opdPatient = OpdPatient::with([
            'patient',
            'doctor',
            'department',
            'charges' => fn ($q) => $q->orderBy('date', 'desc'),
            'charges.charge.chargeCategory',
            'charges.charge.uniteType',
            'transactions' => fn ($q) => $q->orderBy('payment_date', 'desc'),
        ])->findOrFail($id);

        $doctors          = Doctor::select('id', 'name')->get();
        $departments      = Department::select('id', 'name')->get();
        $availableCharges = Charge::with(['chargeCategory'])->get();

        return view('billing.opd_billing.show', compact('opdPatient', 'doctors', 'departments', 'availableCharges'));
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }
}
