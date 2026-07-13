@extends('backend.layouts.master')

@section('title', 'Bill Preview — ' . $admission->icu_case_id)

@section('content')
    <div class="container">
        <div class="app-page-head d-flex justify-content-between">
            <div>
                <h1 class="app-page-title">Bill Preview</h1>
                <div class="text-muted">
                    {{ $admission->icu_case_id }} — {{ $admission->patient?->patient_name }}
                </div>
            </div>
            <div class="d-flex gap-2">
                <form method="POST" action="{{ route('icu.admissions.billing.refresh', $admission->id) }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-arrow-clockwise"></i> Refresh Package Charges
                    </button>
                </form>
                <a href="{{ route('icu.admissions.billing.mode', $admission->id) }}"
                    class="btn btn-sm btn-outline-secondary">Mode</a>
                <a href="{{ route('icu.admissions.show', $admission->id) }}"
                    class="btn btn-sm btn-outline-secondary">Back</a>
            </div>
        </div>

        @if (session('success')) <div class="alert alert-success mt-2">{{ session('success') }}</div> @endif

        <div class="card mt-2">
            <div class="card-body">
                <h6 class="card-title">Active Mode</h6>
                @if ($enrollment)
                    <span class="badge bg-primary me-2">{{ $enrollment->billing_mode }}</span>
                    @if ($enrollment->package)
                        <strong>{{ $enrollment->package->package_name }}</strong>
                        (৳ {{ number_format($enrollment->package->rate, 2) }}/{{ strtolower($enrollment->package->billing_unit) }})
                    @endif
                @else
                    <span class="badge bg-secondary">Itemized (default)</span>
                @endif
            </div>
        </div>

        <div class="row g-2 mt-1">
            <div class="col-md-6">
                <div class="card"><div class="card-body p-2">
                    <h6 class="card-title px-2 pt-2">Package Charges</h6>
                    <table class="table table-sm mb-0">
                        @forelse ($packageLines as $c)
                            <tr>
                                <td class="ps-2"><small>{{ \Illuminate\Support\Carbon::parse($c->date)->format('Y-m-d') }}</small></td>
                                <td>{{ $c->charge_item }}</td>
                                <td class="text-end pe-2 fw-semibold">৳ {{ number_format($c->net_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-muted text-center py-3 small">No package charges posted.</td></tr>
                        @endforelse
                    </table>
                </div></div>
            </div>

            <div class="col-md-6">
                <div class="card"><div class="card-body p-2">
                    <h6 class="card-title px-2 pt-2">Extra / Itemized Charges</h6>
                    <table class="table table-sm mb-0">
                        @forelse ($extraLines as $c)
                            <tr>
                                <td class="ps-2"><small>{{ \Illuminate\Support\Carbon::parse($c->date)->format('Y-m-d') }}</small></td>
                                <td>{{ $c->charge_item }}</td>
                                <td class="text-end pe-2 fw-semibold">৳ {{ number_format($c->net_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-muted text-center py-3 small">No extra charges.</td></tr>
                        @endforelse
                    </table>
                </div></div>
            </div>
        </div>

        @if ($coveredEquipment->isNotEmpty())
            <div class="card mt-2">
                <div class="card-body p-2">
                    <h6 class="card-title px-2 pt-2">Covered by Package (zero amount)</h6>
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr><th class="ps-2">Equipment</th><th>Type</th><th>Period</th><th class="text-end pe-2">Would-be charge</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($coveredEquipment as $u)
                                @php $est = $u->computeAmount($u->end_time); @endphp
                                <tr>
                                    <td class="ps-2">{{ $u->equipment?->equipment_name }}<br>
                                        <small class="text-muted">{{ $u->equipment?->equipment_code }}</small>
                                    </td>
                                    <td>{{ $u->equipment_type }}</td>
                                    <td><small>{{ $u->start_time?->format('Y-m-d H:i') }} → {{ $u->end_time?->format('Y-m-d H:i') }}</small></td>
                                    <td class="text-end pe-2">
                                        <s class="text-muted">৳ {{ number_format($est['amount'], 2) }}</s>
                                        <span class="badge bg-success-subtle text-success ms-2">Covered</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="card mt-2">
            <div class="card-body">
                <table class="table table-sm mb-0 align-middle">
                    <tr><td class="text-end" style="width:240px;">Package Charges</td><td class="text-end fw-semibold">৳ {{ number_format($totals['package'], 2) }}</td></tr>
                    <tr><td class="text-end">Extra / Itemized</td><td class="text-end fw-semibold">৳ {{ number_format($totals['extra'], 2) }}</td></tr>
                    <tr><td class="text-end">Discount</td><td class="text-end">৳ {{ number_format($totals['discount'], 2) }}</td></tr>
                    <tr class="table-active">
                        <td class="text-end"><strong>Total</strong></td>
                        <td class="text-end fs-5 fw-bold">৳ {{ number_format($totals['total'], 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
@endsection
