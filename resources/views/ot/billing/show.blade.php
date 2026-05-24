@extends('backend.layouts.master')
@section('title','Billing — ' . $schedule->schedule_no)
@section('content')
<div class="container-fluid">
    <div class="app-page-head mb-3 d-flex justify-content-between">
        <h1 class="app-page-title">Billing — {{ $schedule->schedule_no }}</h1>
        <a href="{{ route('ot.schedules.show', $schedule->id) }}" class="btn btn-outline-secondary">Back</a>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="row g-3">
        <div class="col-md-7"><div class="card">
            <div class="card-header"><strong>Estimated Charges</strong></div>
            <table class="table mb-0">
                <tbody>
                    <tr><td>OT Room</td><td class="text-end">{{ number_format($estimatedCharges['ot_room'], 2) }}</td></tr>
                    <tr><td>Surgeon Fee</td><td class="text-end">{{ number_format($estimatedCharges['surgeon'], 2) }}</td></tr>
                    <tr><td>Anesthesia Fee</td><td class="text-end">{{ number_format($estimatedCharges['anesthesia'], 2) }}</td></tr>
                    <tr><td>Recovery Room</td><td class="text-end">{{ number_format($estimatedCharges['recovery'], 2) }}</td></tr>
                    <tr><td>Consumables / Implants / Instruments</td><td class="text-end">{{ number_format($estimatedCharges['consumables_total'], 2) }}</td></tr>
                    @if($schedule->emergency_fast_track && $estimatedCharges['emergency_surcharge'] > 0)
                        <tr class="text-danger"><td>Emergency Surcharge (15%)</td><td class="text-end">{{ number_format($estimatedCharges['emergency_surcharge'], 2) }}</td></tr>
                    @endif
                </tbody>
                <tfoot><tr class="table-light"><th>Total</th>
                    <th class="text-end">{{ number_format(
                        $estimatedCharges['ot_room'] + $estimatedCharges['surgeon'] + $estimatedCharges['anesthesia']
                        + $estimatedCharges['recovery'] + $estimatedCharges['consumables_total']
                        + $estimatedCharges['emergency_surcharge'], 2) }}</th>
                </tr></tfoot>
            </table>
        </div></div>

        <div class="col-md-5"><div class="card">
            <div class="card-header"><strong>Actions</strong></div>
            <div class="card-body d-grid gap-2">
                <p class="small text-muted mb-1">Posting charges will create PatientCharge entries for the patient. Consumables get marked as billed.</p>
                <form action="{{ route('ot.billing.post', $schedule->id) }}" method="POST" onsubmit="return confirm('Post charges to patient account?')">@csrf
                    <button class="btn btn-primary w-100"><i class="bi bi-receipt"></i> Post Charges to Patient</button>
                </form>
                <hr class="my-1">
                <a href="{{ route('ot.billing.print', $schedule->id) }}" target="_blank" class="btn btn-outline-secondary">
                    <i class="bi bi-printer"></i> Print Bill
                </a>
                <a href="{{ route('ot.billing.pdf', $schedule->id) }}" class="btn btn-outline-danger">
                    <i class="bi bi-file-earmark-pdf"></i> Download PDF
                </a>
            </div>
        </div></div>
    </div>
</div>
@endsection
