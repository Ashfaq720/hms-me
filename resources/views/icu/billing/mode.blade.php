@extends('backend.layouts.master')

@section('title', 'Billing Mode — ' . $admission->icu_case_id)

@section('content')
    <div class="container">
        <div class="app-page-head d-flex justify-content-between">
            <div>
                <h1 class="app-page-title">Billing Mode & Package</h1>
                <div class="text-muted">{{ $admission->icu_case_id }}</div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('icu.admissions.billing.preview', $admission->id) }}"
                    class="btn btn-sm btn-outline-primary">Bill Preview</a>
                <a href="{{ route('icu.admissions.show', $admission->id) }}"
                    class="btn btn-sm btn-outline-secondary">Back</a>
            </div>
        </div>

        @if (session('success')) <div class="alert alert-success mt-2">{{ session('success') }}</div> @endif
        @if (session('error'))   <div class="alert alert-danger mt-2">{{ session('error') }}</div>   @endif

        @php $current = $enrollments->firstWhere('status', 'Active'); @endphp

        <div class="card mt-2">
            <div class="card-body">
                <h6 class="card-title">Current Mode</h6>
                @if ($current)
                    <div>
                        <span class="badge bg-primary me-2">{{ $current->billing_mode }}</span>
                        @if ($current->package)
                            <strong>{{ $current->package->package_name }}</strong>
                            (৳ {{ number_format($current->package->rate, 2) }}/{{ strtolower($current->package->billing_unit) }})
                        @endif
                        <small class="text-muted ms-2">since
                            {{ $current->start_time?->format('Y-m-d H:i') }}</small>
                    </div>
                @else
                    <div class="text-muted">No active enrollment — defaults to Itemized.</div>
                @endif
            </div>
        </div>

        <div class="card mt-2">
            <div class="card-body">
                <h6 class="card-title">Apply / Change Mode</h6>
                <form method="POST" action="{{ route('icu.admissions.billing.apply', $admission->id) }}"
                    class="row g-2">
                    @csrf
                    <div class="col-md-2">
                        <label class="form-label small">Mode <span class="text-danger">*</span></label>
                        <select name="billing_mode" class="form-select form-select-sm" required>
                            @foreach (['Itemized', 'Package', 'Mixed'] as $m)
                                <option value="{{ $m }}">{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Package (required for Package/Mixed)</label>
                        <select name="package_id" class="form-select form-select-sm">
                            <option value="">--</option>
                            @foreach ($packages as $p)
                                <option value="{{ $p->id }}">
                                    {{ $p->package_name }}
                                    [{{ $p->icu_type ?? 'Any' }}]
                                    — ৳ {{ number_format($p->rate, 2) }}/{{ strtolower($p->billing_unit) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Start Time <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="start_time"
                            value="{{ now()->format('Y-m-d\TH:i') }}" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Approval Reference</label>
                        <input type="text" name="approval_reference" class="form-control form-control-sm"
                            placeholder="(required for backdated)">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label small">Reason <span class="text-danger">*</span></label>
                        <input type="text" name="reason" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label small">Remarks</label>
                        <input type="text" name="remarks" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-12 text-end">
                        <button class="btn btn-primary btn-sm">Apply</button>
                    </div>
                </form>
            </div>
        </div>

        @if ($current && $current->billing_mode !== 'Itemized')
            <div class="card mt-2 border-warning-subtle">
                <div class="card-body">
                    <h6 class="card-title">End Package Coverage</h6>
                    <form method="POST" action="{{ route('icu.admissions.billing.end', $admission->id) }}"
                        class="row g-2">
                        @csrf
                        <div class="col-md-9">
                            <input type="text" name="reason" class="form-control form-control-sm"
                                placeholder="Reason (required)" required>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-warning btn-sm w-100">End → revert to Itemized</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <div class="card mt-2">
            <div class="card-body p-2">
                <h6 class="card-title px-2 pt-2">Enrollment History</h6>
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-2" style="width:140px;">Start</th>
                            <th style="width:140px;">End</th>
                            <th>Mode</th>
                            <th>Package</th>
                            <th style="width:100px;">Status</th>
                            <th>Approval Ref</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($enrollments as $en)
                            <tr>
                                <td class="ps-2"><small>{{ $en->start_time?->format('Y-m-d H:i') }}</small></td>
                                <td><small>{{ $en->end_time?->format('Y-m-d H:i') ?? '-' }}</small></td>
                                <td>{{ $en->billing_mode }}</td>
                                <td>{{ $en->package?->package_name ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-{{ $en->status === 'Active' ? 'primary' : 'secondary' }}">
                                        {{ $en->status }}
                                    </span>
                                </td>
                                <td><small>{{ $en->approval_reference ?? '-' }}</small></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">No enrollments yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($audits->isNotEmpty())
            <div class="card mt-2">
                <div class="card-body p-2">
                    <h6 class="card-title px-2 pt-2">Mode-Change Audit</h6>
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-2" style="width:160px;">When</th>
                                <th>Old → New</th>
                                <th>Reason</th>
                                <th style="width:90px;">By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($audits as $a)
                                <tr>
                                    <td class="ps-2"><small>{{ $a->changed_at?->format('Y-m-d H:i') }}</small></td>
                                    <td><small>{{ $a->old_billing_mode ?? '—' }} → {{ $a->new_billing_mode }}</small></td>
                                    <td><small>{{ $a->reason }}</small></td>
                                    <td>#{{ $a->changed_by ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
