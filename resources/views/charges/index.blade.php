@extends('backend.layouts.master')

@section('title', 'Charges')

@section('content')
    <div class="container-fluid px-3 px-md-4">
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Please fix the following errors:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="row g-4">

            {{-- LEFT: Setup Menu --}}
            <div class="col-xl-3 col-lg-4 col-md-4">
                @include('backend.layouts.charges_setup')
            </div>

            {{-- RIGHT: Content --}}
            <div class="col-xl-9 col-lg-8 col-md-8">
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden">

                    {{-- Header --}}
                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <div>
                                <h5 class="mb-1 fw-semibold text-dark">Charges</h5>
                                <p class="mb-0 text-muted small">Manage all Charges from here</p>
                            </div>

                            <a data-size="lg" class="btn btn-primary btn-sm px-3 d-inline-flex align-items-center"
                                data-url="{{ route('admin.charges.create') }}" data-ajax-popup="true"
                                data-title="Create Charges" data-bs-toggle="tooltip" title="Create Charges">
                                <i class="bi bi-plus-lg me-1"></i> Create Charges
                            </a>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="dt_basic" class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="60">SL</th>
                                        <th>Charge Type</th>
                                        <th>Charge Category</th>
                                        <th>Unit Type</th>
                                        <th>Tax Category</th>
                                        <th>Charge Name</th>
                                        <th>Tax (%)</th>
                                        <th>Standard Charge</th>
                                        <th>Description</th>
                                        <th width="140" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($charges ?? [] as $key => $charge)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $charge->chargeType->name ?? '-' }}</td>
                                            <td>{{ $charge->chargeCategory->name ?? '-' }}</td>
                                            <td>{{ $charge->unitType->name ?? '-' }}</td>
                                            <td>{{ $charge->taxCategory->name ?? '-' }}</td>
                                            <td>{{ $charge->charge_name }}</td>
                                            <td>{{ $charge->tax ?? 0 }}</td>
                                            <td>{{ number_format($charge->standard_charge, 2) }}</td>
                                            <td>{{ \Illuminate\Support\Str::limit($charge->description, 40) }}</td>
                                            <td class="text-center">

                                                <a data-size="lg" class="btn btn-sm btn-outline-warning action-btn"
                                                    data-url="{{ route('admin.charges.edit', $charge->id) }}"
                                                    data-ajax-popup="true" data-title="Edit Charge"
                                                    data-bs-toggle="tooltip" title="Edit Charge">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>

                                                <form action="{{ route('admin.charges.destroy', $charge->id) }}"
                                                    method="POST" class="d-inline"
                                                    onsubmit="return confirm('Are you sure you want to delete this charge?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
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
@endsection

@push('scripts')
    <script>
        $(document).on('change', 'select[name="charge_type_id"]', function() {
            let chargeTypeId = $(this).val();
            let $chargeCategory = $('select[name="charge_category_id"]');

            $chargeCategory.html('<option value="">Loading...</option>');

            if (chargeTypeId) {
                $.ajax({
                    url: "{{ route('admin.charges.get-charge-categories') }}",
                    type: "GET",
                    data: {
                        charge_type_id: chargeTypeId
                    },
                    success: function(response) {
                        $chargeCategory.empty().append('<option value="">Select</option>');

                        $.each(response, function(key, value) {
                            $chargeCategory.append('<option value="' + key + '">' + value +
                                '</option>');
                        });
                    },
                    error: function() {
                        $chargeCategory.html('<option value="">No Data Found</option>');
                    }
                });
            } else {
                $chargeCategory.html('<option value="">Select</option>');
            }
        });
    </script>
@endpush
