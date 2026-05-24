@extends('backend.layouts.master')

@php
    $unitLabel = request('icu_type') ?: 'ICU';
    $createParams = request('icu_type') ? ['icu_type' => request('icu_type')] : [];
@endphp

@section('title', $unitLabel . ' Packages')

@section('content')
    <div class="container">
        <div class="app-page-head d-flex justify-content-between">
            <h1 class="app-page-title">{{ $unitLabel }} Packages</h1>
            <a href="{{ route('icu.packages.create', $createParams) }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> New Package
            </a>
        </div>

        @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
        @if (session('error'))   <div class="alert alert-danger">{{ session('error') }}</div>   @endif

        <div class="card mt-2">
            <div class="card-body p-2">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-2" style="width:35px;">SN</th>
                            <th style="width:120px;">Code</th>
                            <th>Name</th>
                            <th style="width:90px;">ICU Type</th>
                            <th style="width:120px;">Rate</th>
                            <th style="width:80px;">Items</th>
                            <th style="width:90px;">Status</th>
                            <th style="width:160px;" class="text-end pe-2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($packages as $p)
                            <tr>
                                <td class="ps-2 text-muted">{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $p->package_code }}</td>
                                <td>{{ $p->package_name }}</td>
                                <td>{{ $p->icu_type ?? 'Any' }}</td>
                                <td>৳ {{ number_format($p->rate, 2) }}/{{ strtolower($p->billing_unit) }}</td>
                                <td>{{ $p->items_count }}</td>
                                <td>
                                    <span class="badge bg-{{ $p->is_active ? 'success' : 'secondary' }}">
                                        {{ $p->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-end pe-2">
                                    <a href="{{ route('icu.packages.edit', array_merge([$p->id], $createParams)) }}"
                                        class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form action="{{ route('icu.packages.destroy', array_merge([$p->id], $createParams)) }}" method="POST"
                                        class="d-inline" onsubmit="return confirm('Delete this package?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Del</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">No packages yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
