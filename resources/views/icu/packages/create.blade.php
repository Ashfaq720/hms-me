@extends('backend.layouts.master')

@php
    $unitLabel = ($icuType ?? null) ?: 'ICU / CCU';
    $unitQuery = ($icuType ?? null) ? ['icu_type' => $icuType] : [];
@endphp

@section('title', ($package ? 'Edit ' : 'New ') . $unitLabel . ' Package')

@section('content')
    <div class="container">
        <div class="app-page-head d-flex justify-content-between">
            <h1 class="app-page-title">{{ $package ? 'Edit' : 'New' }} {{ $unitLabel }} Package</h1>
            <a href="{{ route('icu.packages.index', $unitQuery) }}" class="btn btn-sm btn-outline-secondary">Back</a>
        </div>

        @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
        @if (session('error'))   <div class="alert alert-danger">{{ session('error') }}</div>   @endif

        <form method="POST"
            action="{{ $package ? route('icu.packages.update', $package->id) : route('icu.packages.store') }}"
            class="mt-2">
            @csrf
            @if ($package) @method('PUT') @endif

            <div class="card">
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label">Code <span class="text-danger">*</span></label>
                            <input type="text" name="package_code"
                                value="{{ old('package_code', $package->package_code ?? '') }}"
                                class="form-control" required>
                            @error('package_code')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="package_name"
                                value="{{ old('package_name', $package->package_name ?? '') }}"
                                class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">ICU Type</label>
                            @php $lockUnit = ! empty($icuType); @endphp
                            <select name="icu_type" class="form-select" {{ $lockUnit ? 'disabled' : '' }}>
                                <option value="">Any</option>
                                @foreach (['ICU', 'CCU', 'NICU', 'PICU'] as $t)
                                    <option value="{{ $t }}"
                                        {{ old('icu_type', $package->icu_type ?? $icuType ?? '') === $t ? 'selected' : '' }}>
                                        {{ $t }}
                                    </option>
                                @endforeach
                            </select>
                            @if ($lockUnit)
                                <input type="hidden" name="icu_type" value="{{ $icuType }}">
                            @endif
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Rate (৳) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="rate"
                                value="{{ old('rate', $package->rate ?? 0) }}" class="form-control" required>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Billing Unit <span class="text-danger">*</span></label>
                            <select name="billing_unit" class="form-select" required>
                                @foreach (['Day', 'Hour', 'Fixed'] as $u)
                                    <option value="{{ $u }}"
                                        {{ old('billing_unit', $package->billing_unit ?? 'Day') === $u ? 'selected' : '' }}>
                                        {{ $u }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" id="active" name="is_active" value="1"
                                    {{ old('is_active', $package->is_active ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="active">Active</label>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Description</label>
                            <input type="text" name="description"
                                value="{{ old('description', $package->description ?? '') }}" class="form-control">
                        </div>
                    </div>

                    <div class="text-end mt-2">
                        <button class="btn btn-primary">{{ $package ? 'Update' : 'Save' }}</button>
                    </div>
                </div>
            </div>
        </form>

        @if ($package)
            <div class="card mt-2">
                <div class="card-body">
                    <h6 class="card-title">Add Rule</h6>
                    <form method="POST" action="{{ route('icu.packages.items.add', $package->id) }}" class="row g-2">
                        @csrf
                        <div class="col-md-2">
                            <label class="form-label small">Category</label>
                            <select name="charge_category" class="form-select form-select-sm" required>
                                @foreach ($categories as $c)
                                    <option value="{{ $c }}">{{ $c }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Code (optional)</label>
                            <input type="text" name="charge_code" class="form-control form-control-sm"
                                placeholder="e.g. Ventilator">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Item Name</label>
                            <input type="text" name="item_name" class="form-control form-control-sm"
                                placeholder="Friendly label">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Rule</label>
                            <select name="rule_type" class="form-select form-select-sm" required>
                                @foreach (['Included', 'Excluded', 'Limited'] as $r)
                                    <option value="{{ $r }}">{{ $r }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label small">Qty</label>
                            <input type="number" name="included_qty" class="form-control form-control-sm" min="1">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Limit Period</label>
                            <select name="limit_period" class="form-select form-select-sm">
                                <option value="">--</option>
                                <option value="PerDay">PerDay</option>
                                <option value="PerStay">PerStay</option>
                            </select>
                        </div>
                        <div class="col-md-12 text-end">
                            <button class="btn btn-primary btn-sm">Add Rule</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mt-2">
                <div class="card-body p-2">
                    <h6 class="card-title px-2 pt-2">Rules ({{ $items->count() }})</h6>
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-2">Category</th>
                                <th>Code</th>
                                <th>Name</th>
                                <th style="width:120px;">Rule</th>
                                <th style="width:80px;">Qty</th>
                                <th style="width:100px;">Period</th>
                                <th style="width:100px;" class="text-end pe-2">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $i)
                                @php
                                    $rCol = match ($i->rule_type) {
                                        'Included' => 'success',
                                        'Excluded' => 'danger',
                                        'Limited'  => 'warning',
                                        default    => 'secondary',
                                    };
                                @endphp
                                <tr>
                                    <td class="ps-2">{{ $i->charge_category }}</td>
                                    <td>{{ $i->charge_code ?? '*' }}</td>
                                    <td>{{ $i->item_name ?? '-' }}</td>
                                    <td><span class="badge bg-{{ $rCol }}">{{ $i->rule_type }}</span></td>
                                    <td>{{ $i->included_qty ?? '-' }}</td>
                                    <td>{{ $i->limit_period ?? '-' }}</td>
                                    <td class="text-end pe-2">
                                        <form method="POST"
                                            action="{{ route('icu.packages.items.delete', [$package->id, $i->id]) }}"
                                            class="d-inline" onsubmit="return confirm('Remove this rule?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">Del</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-3">No rules yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
