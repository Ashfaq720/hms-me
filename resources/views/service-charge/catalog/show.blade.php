@extends('backend.layouts.master')

@section('title', $catalog->name)

@section('content')
    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div>
                <h1 class="app-page-title">{{ $catalog->name }}</h1>
                <p class="text-muted small mb-0">Code: <code>{{ $catalog->code }}</code></p>
            </div>
            <div class="d-flex gap-2">
                @can('service_charge.manage')
                    <a href="{{ route('service-charge.catalog.edit', $catalog) }}" class="btn btn-primary">Edit</a>
                @endcan
                <a href="{{ route('service-charge.catalog.index') }}" class="btn btn-outline-secondary">Back</a>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Pricing</h5>
                        <dl class="row mb-0">
                            <dt class="col-sm-5">Service type</dt>
                            <dd class="col-sm-7"><span class="badge bg-info-soft">{{ $catalog->service_type }}</span></dd>

                            <dt class="col-sm-5">Charge unit</dt>
                            <dd class="col-sm-7">{{ $catalog->charge_unit }}</dd>

                            <dt class="col-sm-5">Base price</dt>
                            <dd class="col-sm-7">{{ number_format((float) $catalog->base_price, 2) }}</dd>

                            <dt class="col-sm-5">Tax %</dt>
                            <dd class="col-sm-7">{{ number_format((float) $catalog->tax_percent, 2) }}</dd>

                            <dt class="col-sm-5">Patient type</dt>
                            <dd class="col-sm-7">{{ ucfirst($catalog->patient_type) }}</dd>

                            <dt class="col-sm-5">Validity</dt>
                            <dd class="col-sm-7">
                                {{ optional($catalog->valid_from)->toDateString() ?? '—' }}
                                &rarr;
                                {{ optional($catalog->valid_to)->toDateString() ?? '—' }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Flags</h5>
                        <ul class="list-unstyled mb-0">
                            <li>{!! $catalog->discount_allowed ? '<i class="text-success">●</i>' : '<i class="text-muted">○</i>' !!} Discount allowed</li>
                            <li>{!! $catalog->insurance_covered ? '<i class="text-success">●</i>' : '<i class="text-muted">○</i>' !!} Insurance covered</li>
                            <li>{!! $catalog->package_eligible ? '<i class="text-success">●</i>' : '<i class="text-muted">○</i>' !!} Package eligible</li>
                            <li>{!! $catalog->is_active ? '<i class="text-success">●</i>' : '<i class="text-muted">○</i>' !!} Active</li>
                        </ul>
                        @if ($catalog->description)
                            <hr>
                            <p class="text-muted mb-0">{{ $catalog->description }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header"><strong>Active rules</strong></div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Rule kind</th>
                            <th>Rule value</th>
                            <th>Adjustment</th>
                            <th>Priority</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($catalog->rules as $rule)
                            <tr>
                                <td>{{ $rule->rule_kind }}</td>
                                <td>{{ $rule->rule_value }}</td>
                                <td>{{ $rule->adjustment_type }} {{ $rule->adjustment_value }}</td>
                                <td>{{ $rule->priority }}</td>
                                <td>{!! $rule->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' !!}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-3 text-muted">No rules configured. The base price will be used.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header"><strong>Recent postings</strong></div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Encounter</th>
                            <th>Trigger event</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Net amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($catalog->postings->take(10) as $posting)
                            <tr>
                                <td>{{ $posting->created_at?->toDateTimeString() }}</td>
                                <td>#{{ $posting->encounter_id ?? '—' }}</td>
                                <td><code>{{ $posting->trigger_event }}</code></td>
                                <td class="text-end">{{ number_format((float) $posting->quantity, 2) }}</td>
                                <td class="text-end">{{ number_format((float) $posting->net_amount, 2) }}</td>
                                <td>{{ $posting->status }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-3 text-muted">No postings yet for this service.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
