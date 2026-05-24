<?php

namespace App\Http\Controllers\OPD;

use App\Http\Controllers\Controller;
use App\Models\OpdPatient;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OpdPaymentController extends Controller
{
    public function create($id)
    {
        $opdPatient = OpdPatient::with(['patient', 'doctor', 'department'])->findOrFail($id);

        return view('opd_patients.payments.create', compact('opdPatient'));
    }

    public function store(Request $request, $opdPatientId)
    {
        $opdPatient = OpdPatient::findOrFail($opdPatientId);
        $data       = $this->validateData($request);

        $storedFiles = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                if ($file && $file->isValid()) {
                    $storedFiles[] = $file->store('transactions', 'public');
                }
            }
        }

        $data = $this->cleanByVia($data);
        $data['vat']      ??= 0;
        $data['tax']      ??= 0;
        $data['discount'] ??= 0;
        $data['status']   ??= 'successed';
        $data['patient_id']     = $opdPatient->patient_id;
        $data['opd_patient_id'] = $opdPatient->id;
        $data['case_id']        = $opdPatient->case_id;
        $data['type']           = $data['type'] ?? 'payment';
        $data['section']        = 'opd';
        $data['net_amount']     = $this->calcNetAmount($data);
        $data['files']          = ! empty($storedFiles) ? json_encode($storedFiles) : null;

        Transaction::create($data);

        if ($request->input('source') === 'billing') {
            return back()->with('success', 'Payment created successfully.');
        }

        return redirect(route('opd-patients.show', $opdPatient->id) . '?tab=payments')
            ->with('success', 'Payment created successfully.');
    }

    public function edit($opdPatientId, $paymentId)
    {
        $opdPatient = OpdPatient::with(['patient', 'doctor', 'department'])->findOrFail($opdPatientId);
        $payment    = Transaction::where('opd_patient_id', $opdPatientId)->findOrFail($paymentId);

        return view('opd_patients.payments.edit', compact('opdPatient', 'payment'));
    }

    public function update(Request $request, $opdPatientId, $paymentId)
    {
        $opdPatient = OpdPatient::findOrFail($opdPatientId);
        $payment    = Transaction::where('opd_patient_id', $opdPatientId)->findOrFail($paymentId);

        $data = $this->validateData($request);

        $storedFiles = $payment->files ? (json_decode($payment->files, true) ?: []) : [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                if ($file && $file->isValid()) {
                    $storedFiles[] = $file->store('transactions', 'public');
                }
            }
        }

        $data = $this->cleanByVia($data);
        $data['vat']        ??= 0;
        $data['tax']        ??= 0;
        $data['discount']   ??= 0;
        $data['status']     ??= 'successed';
        $data['net_amount'] = $this->calcNetAmount($data);
        $data['files']      = ! empty($storedFiles) ? json_encode($storedFiles) : null;

        $payment->update($data);

        if ($request->input('source') === 'billing') {
            return back()->with('success', 'Payment updated successfully.');
        }

        return redirect(route('opd-patients.show', $opdPatient->id) . '?tab=payments')
            ->with('success', 'Payment updated successfully.');
    }

    public function destroy(Request $request, $opdPatientId, $transactionId)
    {
        $payment = Transaction::where('opd_patient_id', $opdPatientId)->findOrFail($transactionId);

        if ($payment->files) {
            $files = json_decode($payment->files, true) ?: [];
            foreach ($files as $f) {
                if (Storage::disk('public')->exists($f)) {
                    Storage::disk('public')->delete($f);
                }
            }
        }

        $payment->delete();

        if ($request->input('source') === 'billing') {
            return back()->with('success', 'Payment deleted successfully.');
        }

        return redirect(route('opd-patients.show', $opdPatientId) . '?tab=payments')
            ->with('success', 'Payment deleted successfully.');
    }

    protected function calcNetAmount(array $data): float
    {
        $amount      = (float) ($data['amount'] ?? 0);
        $vatPct      = (float) ($data['vat'] ?? 0);
        $taxPct      = (float) ($data['tax'] ?? 0);
        $discountPct = (float) ($data['discount'] ?? 0);

        $vatAmt      = $amount * $vatPct / 100;
        $taxAmt      = $amount * $taxPct / 100;
        $subtotal    = $amount + $vatAmt + $taxAmt;
        $discountAmt = $subtotal * $discountPct / 100;

        return round($subtotal - $discountAmt, 2);
    }

    protected function cleanByVia(array $data): array
    {
        $via = $data['payment_via'] ?? null;
        if ($via !== 'card') {
            $data['card_no']   = null;
            $data['card_type'] = null;
        }
        if ($via !== 'cheque') {
            $data['cheque_name'] = null;
            $data['cheque_no']   = null;
            $data['cheque_date'] = null;
        }
        if ($via !== 'mfs') {
            $data['mfs_type']           = null;
            $data['mfs_no']             = null;
            $data['mfs_transaction_id'] = null;
        }
        return $data;
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'type'               => ['nullable', 'string', 'max:30'],
            'amount'             => ['required', 'numeric', 'min:0'],
            'vat'                => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tax'                => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount'           => ['nullable', 'numeric', 'min:0', 'max:100'],
            'payment_via'        => ['required', 'in:cash,card,cheque,mfs,other'],
            'payment_date'       => ['required', 'date'],
            'received_by'        => ['nullable', 'string', 'max:100'],
            'cheque_name'        => ['nullable', 'string', 'max:30'],
            'cheque_no'          => ['nullable', 'string', 'max:30'],
            'cheque_date'        => ['nullable', 'date'],
            'card_no'            => ['nullable', 'string', 'max:30'],
            'card_type'          => ['nullable', 'in:visa,master,american_express,other'],
            'mfs_type'           => ['nullable', 'in:bkash,nagad,rocket,other'],
            'mfs_no'             => ['nullable', 'string', 'max:30'],
            'mfs_transaction_id' => ['nullable', 'string', 'max:100'],
            'notes'              => ['nullable', 'string'],
            'status'             => ['nullable', 'in:pending,successed,failed,canceled'],
            'files'              => ['nullable', 'array'],
            'files.*'            => ['file', 'max:5120'],
        ]);
    }
}
