<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0"><i class="bi bi-box-seam"></i> Package Enrollments</h6>
        <div class="d-flex gap-2">
            <a href="javascript:void(0);" class="btn btn-sm btn-primary"
                data-url="{{ route('ipd-patients.packages.enroll', $iPDPatient->id) }}"
                data-size="lg" data-ajax-popup="true"
                data-title="Enrol Package">
                <i class="bi bi-plus-lg"></i> Enrol Package
            </a>
            <a href="{{ route('packages.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-list"></i> Browse Packages
            </a>
        </div>
    </div>

    @forelse ($enrollments as $enrollment)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div>
                    <strong>{{ $enrollment->package->name ?? '—' }}</strong>
                    <span class="badge bg-primary bg-opacity-10 text-primary ms-2">
                        {{ $enrollment->package->package_type ?? 'PACKAGE' }}
                    </span>
                    <span class="text-muted small ms-2">
                        #{{ $enrollment->enrollment_no }}
                    </span>
                </div>
                <div>
                    <span class="badge bg-{{ $enrollment->status === 'active' ? 'success' : ($enrollment->status === 'completed' ? 'info' : 'secondary') }}">
                        {{ ucfirst($enrollment->status) }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-2 mb-3">
                    <div class="col-md-3">
                        <small class="text-muted d-block">Start Date</small>
                        <strong>{{ $enrollment->start_date?->toDateString() ?? '—' }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">End Date</small>
                        <strong>{{ $enrollment->end_date?->toDateString() ?? '—' }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Agreed Price</small>
                        <strong class="text-success">৳ {{ number_format((float) $enrollment->agreed_price, 2) }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Paid Amount</small>
                        <strong class="text-primary">৳ {{ number_format((float) $enrollment->paid_amount, 2) }}</strong>
                    </div>
                </div>

                @if ($enrollment->package && $enrollment->package->services->count())
                    <h6 class="mt-3 mb-2 small text-uppercase text-muted">Included Services & Consumption</h6>
                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Service</th>
                                <th class="text-center">Allowed</th>
                                <th class="text-center">Consumed</th>
                                <th class="text-center">Extras</th>
                                <th class="text-end">Rate</th>
                                <th class="text-end">Amount</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($enrollment->package->services as $ps)
                                @php
                                    $entry = $enrollment->entries->firstWhere('package_service_id', $ps->id);
                                    $allowed = (float) ($entry?->quantity_allowed ?? $ps->quantity);
                                    $consumed = (float) ($entry?->quantity_consumed ?? 0);
                                    $extras = (float) ($entry?->quantity_extras ?? 0);
                                    $remaining = max($allowed - $consumed, 0);
                                @endphp
                                <tr>
                                    <td>{{ $ps->service->name ?? '—' }}</td>
                                    <td class="text-center">{{ rtrim(rtrim(number_format($allowed, 2), '0'), '.') }}</td>
                                    <td class="text-center">{{ rtrim(rtrim(number_format($consumed, 2), '0'), '.') }}</td>
                                    <td class="text-center">
                                        @if ($extras > 0)
                                            <span class="badge bg-warning text-dark">{{ rtrim(rtrim(number_format($extras, 2), '0'), '.') }}</span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="text-end">{{ number_format((float) $ps->rate, 2) }}</td>
                                    <td class="text-end">{{ number_format((float) $ps->amount, 2) }}</td>
                                    <td class="text-center">
                                        @if ($remaining <= 0 && $consumed > 0)
                                            <span class="badge bg-success">Used</span>
                                        @elseif ($consumed > 0)
                                            <span class="badge bg-info">{{ rtrim(rtrim(number_format($remaining, 2), '0'), '.') }} left</span>
                                        @else
                                            <span class="badge bg-secondary">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-muted small mb-0">This package has no itemised services configured.</p>
                @endif

                @if ($enrollment->notes)
                    <div class="mt-2 small text-muted">
                        <i class="bi bi-info-circle"></i> {{ $enrollment->notes }}
                    </div>
                @endif

                @php
                    $remaining = max((float) $enrollment->agreed_price - (float) $enrollment->paid_amount, 0);
                @endphp
                <div class="mt-3 p-2 bg-light rounded d-flex flex-wrap justify-content-between align-items-center gap-2 small">
                    <div>
                        <i class="bi bi-cash-coin text-success"></i>
                        <strong>Outstanding:</strong>
                        <span class="text-{{ $remaining > 0.01 ? 'danger' : 'success' }}">৳ {{ number_format($remaining, 2) }}</span>
                    </div>
                    @if ($linkedBills->count())
                        <div>
                            <span class="text-muted">Linked bill:</span>
                            @foreach ($linkedBills as $bill)
                                @can('billing.bill.view')
                                    <a href="{{ route('billing.bills.show', $bill->id) }}" class="badge bg-info bg-opacity-10 text-info text-decoration-none">
                                        {{ $bill->bill_no }} ({{ $bill->status }})
                                    </a>
                                @else
                                    <span class="badge bg-info bg-opacity-10 text-info">{{ $bill->bill_no }}</span>
                                @endcan
                            @endforeach
                        </div>
                    @endif
                    <form method="POST" action="{{ route('ipd-patients.packages.destroy', [$iPDPatient->id, $enrollment->id]) }}"
                        onsubmit="return confirm('Cancel this package enrollment?')" class="m-0">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" type="submit">
                            <i class="bi bi-x-circle"></i> Cancel Enrolment
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="text-center py-5 text-muted">
            <i class="bi bi-box display-4"></i>
            <p class="mt-3 mb-1">No package enrolled for this patient.</p>
            <p class="small">Browse packages to enrol the patient in a treatment bundle.</p>
            <a href="{{ route('packages.index') }}" class="btn btn-sm btn-primary mt-2">
                <i class="bi bi-plus-lg"></i> Browse Packages
            </a>
        </div>
    @endforelse
</div>
