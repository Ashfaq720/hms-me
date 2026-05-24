@extends('backend.layouts.master')

@section('title', 'Inventory Items')

@section('content')
    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div>
                <h1 class="app-page-title">Inventory Items</h1>
                <p class="text-muted small mb-0">Unified item master across pharmacy, OT, ICU and consumables (SRS &sect;5.20).</p>
            </div>
            @can('inventory.item.manage')
                <a href="{{ route('inventory.items.create') }}" class="btn btn-primary">
                    <i class="fi fi-rr-plus me-1"></i> Add Item
                </a>
            @endcan
        </div>

        @if (session('success'))
            <div class="alert alert-success mt-3">{{ session('success') }}</div>
        @endif

        <form method="GET" class="row g-2 mt-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label small text-muted">Search</label>
                <input type="text" name="q" class="form-control" placeholder="name / code / generic / brand..." value="{{ request('q') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Category</label>
                <select name="category" class="form-select">
                    <option value="">All categories</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat }}" @selected(request('category') === $cat)>{{ ucfirst($cat) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Per page</label>
                <select name="per_page" class="form-select" onchange="this.form.submit()">
                    @foreach (['10', '25', '50', '100', 'all'] as $opt)
                        <option value="{{ $opt }}" @selected((string) $perPage === $opt)>{{ $opt === 'all' ? 'All' : $opt }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-primary w-100">Filter</button>
            </div>
        </form>

        <div class="d-flex justify-content-between align-items-center mt-3 mb-2">
            <small class="text-muted">
                Showing <strong>{{ $items->count() }}</strong> of <strong>{{ $total }}</strong> items
                @if ($perPage !== 'all' && $items->lastPage() > 1)
                    · page {{ $items->currentPage() }} of {{ $items->lastPage() }}
                @endif
            </small>
            <small class="text-muted">
                @if (request()->has('q') || request()->has('category'))
                    <a href="{{ route('inventory.items.index') }}" class="text-decoration-none"><i class="fi fi-rr-cross"></i> Clear filters</a>
                @endif
            </small>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 4%;">#</th>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Generic / Brand</th>
                                <th>UOM</th>
                                <th class="text-end">Reorder</th>
                                <th>Flags</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $idx => $item)
                                <tr>
                                    <td>{{ ($items->currentPage() - 1) * $items->perPage() + $loop->iteration }}</td>
                                    <td><code>{{ $item->code }}</code></td>
                                    <td><strong>{{ $item->name }}</strong></td>
                                    <td><span class="badge bg-info-soft">{{ $item->category ?? '—' }}</span></td>
                                    <td>
                                        @if ($item->generic_name) <small>{{ $item->generic_name }}</small><br> @endif
                                        @if ($item->brand) <small class="text-muted">{{ $item->brand }}</small> @endif
                                    </td>
                                    <td>{{ $item->uom }}</td>
                                    <td class="text-end">{{ number_format((float) $item->reorder_level, 2) }}</td>
                                    <td>
                                        @if ($item->is_controlled) <span class="badge bg-warning" title="Controlled drug">C</span> @endif
                                        @if ($item->is_consumable) <span class="badge bg-secondary" title="Consumable">Cnsm</span> @endif
                                        @if ($item->is_asset) <span class="badge bg-dark" title="Asset">A</span> @endif
                                    </td>
                                    <td>
                                        @if ($item->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('inventory.items.show', $item) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                        @can('inventory.item.manage')
                                            <a href="{{ route('inventory.items.edit', $item) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="text-center py-4 text-muted">No items match your filter.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($items->hasPages())
                <div class="card-footer">{{ $items->links() }}</div>
            @endif
        </div>
    </div>
@endsection
