@extends('backend.layouts.master')

@section('title', 'Doctors')

@section('content')
    <div class="container">
        {{-- Page Head --}}
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div class="clearfix">
                <h1 class="app-page-title">Doctors</h1>
            </div>

            <a href="{{ route('doctors.create') }}" class="btn btn-primary waves-effect waves-light">
                <i class="fi fi-rr-plus me-1"></i> Add Doctor
            </a>
        </div>

        {{-- Table --}}
        <div class="row mt-4">
            <div class="col-12">
                <div class="card overflow-hidden">
                    <div
                        class="card-header d-flex flex-wrap gap-3 align-items-center justify-content-between border-0 pb-0">
                        <h6 class="card-title mb-2">Doctors List</h6>
                        <div id="dt_doctors_Search"></div>
                    </div>

                    <div class="card-body px-3 pt-2 pb-0 gradient-layer">
                        <table id="dt_doctors" class="table display table-row-rounded">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Image</th>
                                    <th>Doctor</th>
                                    <th>Department</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>Reg. No</th>
                                    <th>Active</th>
                                    <th width="220">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($doctors as $d)
                                    <tr>
                                        <td>{{ $d->id }}</td>

                                        <td>
                                            @if ($d->image)
                                                <img src="{{ asset('storage/' . $d->image) }}" width="42" height="42"
                                                    style="object-fit:cover;border-radius:8px;">
                                            @else
                                                <div class="text-dark">No image</div>
                                            @endif
                                        </td>

                                        <td>
                                            <div class="fw-bold">{{ $d->name }}</div>
                                            <div class="text-dark">
                                                Code: <span class="fw-semibold">{{ $d->doctor_code }}</span>
                                                @if (!empty($d->designation?->name))
                                                    | {{ $d->designation->name }}
                                                @endif
                                                @if (!empty($d->specialist?->name))
                                                    | {{ $d->specialist->name }}
                                                @endif
                                            </div>
                                        </td>

                                        <td>
                                            <div class="fw-semibold">{{ $d->department?->name ?? '' }}</div>
                                            <div class="text-dark">
                                                Type: {{ $d->doctor_type ?? '' }}
                                            </div>
                                        </td>

                                        <td>
                                            <div>{{ $d->phone ?? '' }}</div>
                                            <div class="text-dark">
                                                <span class="fw-semibold">Emergency Contact:</span> <span
                                                    class="bg-warning">{{ $d->emergency_phone ?? '' }}</span>
                                            </div>
                                        </td>

                                        <td>{{ $d->email ?? '' }}</td>

                                        <td>
                                            <div>{{ $d->registration_no ?? '' }}</div>
                                            <div class="text-dark">
                                                License: {{ $d->license_no ?? '' }}
                                                @if (!empty($d->license_expiry_date))
                                                    | Exp:
                                                    {{ \Carbon\Carbon::parse($d->license_expiry_date)->format('Y-m-d') }}
                                                @endif
                                            </div>
                                        </td>

                                        <td>
                                            @if ($d->is_active)
                                                <span class="badge bg-success">Yes</span>
                                            @else
                                                <span class="badge bg-secondary">No</span>
                                            @endif
                                        </td>

                                        <td class="text-nowrap">
                                            <div class="d-flex flex-wrap gap-1 justify-content-start">
                                                <a class="btn btn-sm btn-info" title="View"
                                                    href="{{ route('doctors.show', $d) }}">
                                                    <i class="fa-solid fa-eye"></i>
                                                </a>

                                                <a class="btn btn-sm btn-warning" title="Edit"
                                                    href="{{ route('doctors.edit', $d) }}">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>

                                                <form title="Delete" method="POST"
                                                    action="{{ route('doctors.destroy', $d) }}"
                                                    onsubmit="return confirm('Delete this doctor?')" class="m-0">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-danger" type="submit">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No data found</td>
                                    </tr>
                                @endforelse
                            </tbody>

                        </table>

                        {{-- If you paginate --}}
                        <div class="mt-3 pb-3">
                            {{ $doctors->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
