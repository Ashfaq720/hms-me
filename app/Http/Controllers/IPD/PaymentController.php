<?php
namespace App\Http\Controllers\IPD;

use App\Http\Controllers\Controller;
use App\Models\IpdPatient;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    public function create($ipdPatientId)
    {
        $ipdPatient = IpdPatient::with(['patient', 'doctor', 'department'])->findOrFail($ipdPatientId);
        return view('ipd_patients.payments.create', compact('ipdPatient'));
    }

    public function store(Request $request, $ipdPatientId)
    {
        $ipdPatient = IpdPatient::findOrFail($ipdPatientId);
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
        $data['vat'] ??= 0;
        $data['tax'] ??= 0;
        $data['discount'] ??= 0;
        $data['patient_id']     = $ipdPatient->patient_id;
        $data['ipd_patient_id'] = $ipdPatient->id;
        $data['case_id']        = $ipdPatient->case_id;
        $data['type']           = $data['type'] ?? 'payment';
        $data['section']        = 'ipd';
        $data['net_amount']     = $this->calcNetAmount($data);
        $data['files']          = ! empty($storedFiles) ? json_encode($storedFiles) : null;

        Transaction::create($data);

        if ($request->input('source') === 'billing') {
            return back()->with('success', 'Payment created successfully.');
        }

        // return redirect()
        //     ->route('ipd-patients.ipd-patients.show', $ipdPatient->id)
        //     ->with('success', 'Payment created successfully.');

        return redirect(route('ipd-patients.ipd-patients.show', $ipdPatient->id) . '?tab=payments')
            ->with('success', 'Payment created successfully.');
    }

    public function edit($ipdPatientId, $paymentId)
    {
        $ipdPatient = IpdPatient::with(['patient', 'doctor', 'department'])->findOrFail($ipdPatientId);
        $payment    = Transaction::where('ipd_patient_id', $ipdPatientId)->findOrFail($paymentId);
        return view('ipd_patients.payments.edit', compact('ipdPatient', 'payment'));
    }

    public function update(Request $request, $ipdPatientId, $paymentId)
    {
        $ipdPatient = IpdPatient::findOrFail($ipdPatientId);
        $payment    = Transaction::where('ipd_patient_id', $ipdPatientId)->findOrFail($paymentId);

        $data = $this->validateData($request);

        $storedFiles = $payment->files ? (json_decode($payment->files, true) ?: []): [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                if ($file && $file->isValid()) {
                    $storedFiles[] = $file->store('transactions', 'public');
                }
            }
        }

        $data = $this->cleanByVia($data);
        $data['vat'] ??= 0;
        $data['tax'] ??= 0;
        $data['discount'] ??= 0;
        $data['net_amount'] = $this->calcNetAmount($data);
        $data['files']      = ! empty($storedFiles) ? json_encode($storedFiles) : null;

        $payment->update($data);

        if ($request->input('source') === 'billing') {
            return back()->with('success', 'Payment updated successfully.');
        }

        // return redirect()
        //     ->route('ipd-patients.ipd-patients.show', $ipdPatient->id)
        //     ->with('success', 'Payment updated successfully.');

        return redirect(route('ipd-patients.ipd-patients.show', $ipdPatient->id) . '?tab=payments')
            ->with('success', 'Payment updated successfully.');
    }

    public function destroy(Request $request, $ipdPatientId, $paymentId)
    {
        $payment = Transaction::where('ipd_patient_id', $ipdPatientId)->findOrFail($paymentId);

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

        // return redirect()
        //     ->route('ipd-patients.ipd-patients.show', $ipdPatientId)
        //     ->with('success', 'Payment deleted successfully.');

        return redirect(route('ipd-patients.ipd-patients.show', $ipdPatientId) . '?tab=payments')
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
            'status'             => ['required', 'in:pending,successed,failed,canceled'],
            'files'              => ['nullable', 'array'],
            'files.*'            => ['file', 'max:5120'],
        ]);
    }
}
