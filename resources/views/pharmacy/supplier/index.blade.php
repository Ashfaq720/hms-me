@extends('backend.layouts.master')

@section('title', 'Supplier')

@section('content')
<div class="container-fluid px-3 px-md-4">
    <div class="row g-4">
        <div class="col-xl-3 col-lg-4 col-md-4">
            @include('backend.layouts.medicine_setup')
        </div>

        <div class="col-xl-9 col-lg-8 col-md-8">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h5 class="mb-1 fw-semibold text-dark">List of suppliers</h5>
                            <p class="mb-0 text-muted small">Manage all suppliers from here</p>
                        </div>

                        <a data-size="xl" class="btn btn-primary btn-sm px-3 d-inline-flex align-items-center"
                           data-url="{{ route('admin.suppliers.create') }}"
                           data-ajax-popup="true" data-title="Add a supplier">
                            <i class="bi bi-plus-lg me-1"></i> Add a supplier
                        </a>
                    </div>
                </div>

                @include('pharmacy.supplier.validation-errors')

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="dt_basic">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4 py-3">Supplier name</th>
                                    <th class="py-3">Contact Supplier</th>
                                    <th class="py-3">contact name of a person</th>
                                    <th class="py-3">Contact Person Telephone</th>
                                    <th class="py-3">Drug license number</th>
                                    <th class="py-3">Address</th>
                                    <th class="py-3 text-center" style="width: 140px;">action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($suppliers as $supplier)
                                    <tr>
                                        <td class="px-4 py-3">{{ $supplier->supplier_name }}</td>
                                        <td class="py-3">{{ $supplier->contact_supplier }}</td>
                                        <td class="py-3">{{ $supplier->contact_person_name }}</td>
                                        <td class="py-3">{{ $supplier->contact_person_telephone }}</td>
                                        <td class="py-3">{{ $supplier->drug_license_number }}</td>
                                        <td class="py-3">{{ $supplier->address }}</td>
                                        <td class="py-3 text-center">
                                            <div class="d-inline-flex gap-1">
                                                <a data-size="xl" class="btn btn-sm btn-outline-warning"
                                                   data-url="{{ route('admin.suppliers.edit', $supplier->id) }}"
                                                   data-ajax-popup="true" data-title="Edit supplier">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>
                                                <form method="POST" action="{{ route('admin.suppliers.destroy', $supplier->id) }}" onsubmit="return confirm('Delete this data?')" class="m-0">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(session('modal_type') === 'create')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelector('[data-url="{{ route('pharmacy.suppliers.create') }}"]')?.click();
});
</script>
@endif

@if(session('modal_type') === 'edit' && session('edit_id'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelector('[data-url="{{ route('pharmacy.suppliers.edit', session('edit_id')) }}"]')?.click();
});
</script>
@endif
@endsection
