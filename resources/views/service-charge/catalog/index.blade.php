@extends('backend.layouts.master')

@section('title', 'Service Catalog')

@section('content')
    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div>
                <h1 class="app-page-title">Service Catalog</h1>
                <p class="text-muted small mb-0">Configurable pricing for every billable service (SRS &sect;5.18).</p>
            </div>

            @can('service_charge.manage')
                <a href="{{ route('service-charge.catalog.create') }}" class="btn btn-primary">
                    <i class="fi fi-rr-plus me-1"></i> Add Service
                </a>
            @endcan
        </div>

        @if (session('success'))
            <div class="alert alert-success mt-3">{{ session('success') }}</div>
        @endif

        <form method="GET" class="row g-2 mt-3">
            <div class="col-md-6">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search by name or code...">
            </div>
            <div class="col-md-3">
                <select name="type" class="form-select">
                    <option value="">All service types</option>
                    @foreach (['consultation', 'bed', 'icu_bed', 'nicu_bed', 'ot_room', 'nursing', 'procedure', 'lab_test', 'radiology', 'pharmacy', 'equipment', 'ambulance', 'package', 'administrative', 'other'] as $t)
                        <option value="{{ $t }}" @selected(request('type') === $t)>{{ ucwords(str_replace('_', ' ', $t)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-outline-primary w-100">Filter</button>
            </div>
        </form>

        <div class="card mt-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Service type</th>
                                <th>Charge unit</th>
                                <th class="text-end">Base price</th>
                                <th class="text-end">Tax %</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($catalogs as $catalog)
                                <tr>
                                    <td><code>{{ $catalog->code }}</code></td>
                                    <td>{{ $catalog->name }}</td>
                                    <td><span class="badge bg-secondary-soft">{{ $catalog->service_type }}</span></td>
                                    <td>{{ $catalog->charge_unit }}</td>
                                    <td class="text-end">{{ number_format((float) $catalog->base_price, 2) }}</td>
                                    <td class="text-end">{{ number_format((float) $catalog->tax_percent, 2) }}</td>
                                    <td>
                                        @if ($catalog->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('service-charge.catalog.show', $catalog) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                        @can('service_charge.manage')
                                            <a href="{{ route('service-charge.catalog.edit', $catalog) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">No service catalog entries yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $catalogs->links() }}
            </div>
        </div>
    </div>
@endsection
