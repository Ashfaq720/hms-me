@extends('backend.layouts.master')

@section('title', 'Patients')

@section('content')
    <div class="container">
        {{-- Page Head --}}
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div class="clearfix">
                <h1 class="app-page-title">Patients</h1>
            </div>
            <a href="{{ route('patients.create') }}" class="btn btn-primary waves-effect waves-light">
                <i class="fi fi-rr-plus me-1"></i> Add Patient
            </a>
        </div>

        {{-- Stats --}}
        <div class="row g-3 mt-1 mb-3">
            <div class="col-6 col-md-3">
                <div class="card border-0 bg-primary bg-opacity-10 h-100">
                    <div class="card-body py-3 px-3 d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-primary bg-opacity-25 d-flex align-items-center justify-content-center flex-shrink-0"
                            style="width:44px;height:44px">
                            <i class="fa-solid fa-users text-primary"></i>
                        </div>
                        <div>
                            <div class="fs-4 fw-bold lh-1">{{ number_format($stats['total']) }}</div>
                            <div class="text-muted small mt-1">Total Patients</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 bg-success bg-opacity-10 h-100">
                    <div class="card-body py-3 px-3 d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-success bg-opacity-25 d-flex align-items-center justify-content-center flex-shrink-0"
                            style="width:44px;height:44px">
                            <i class="fa-solid fa-user-check text-success"></i>
                        </div>
                        <div>
                            <div class="fs-4 fw-bold lh-1">{{ number_format($stats['active']) }}</div>
                            <div class="text-muted small mt-1">Active</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 bg-warning bg-opacity-10 h-100">
                    <div class="card-body py-3 px-3 d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-warning bg-opacity-25 d-flex align-items-center justify-content-center flex-shrink-0"
                            style="width:44px;height:44px">
                            <i class="fa-solid fa-bed-pulse text-warning"></i>
                        </div>
                        <div>
                            <div class="fs-4 fw-bold lh-1">{{ number_format($stats['ipd']) }}</div>
                            <div class="text-muted small mt-1">IPD</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 bg-secondary bg-opacity-10 h-100">
                    <div class="card-body py-3 px-3 d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-secondary bg-opacity-25 d-flex align-items-center justify-content-center flex-shrink-0"
                            style="width:44px;height:44px">
                            <i class="fa-solid fa-ribbon text-secondary"></i>
                        </div>
                        <div>
                            <div class="fs-4 fw-bold lh-1">{{ number_format($stats['deceased']) }}</div>
                            <div class="text-muted small mt-1">Deceased</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="row">
            <div class="col-12">
                <div class="card overflow-hidden">
                    <div class="card-header d-flex flex-wrap gap-3 align-items-center justify-content-between border-0 pb-0">
                        <h6 class="card-title mb-2">Patient List</h6>
                    </div>

                    <div class="card-body px-3 pt-2 pb-0">
                        <div class="table-responsive">
                            <table class="table table-row-rounded">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Patient</th>
                                        <th>Contact</th>
                                        <th>Demographics</th>
                                        <th>Status</th>
                                        <th width="150">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($patients as $p)
                                        @php
                                            $words    = array_filter(explode(' ', $p->patient_name));
                                            $initials = strtoupper(
                                                substr($words[array_key_first($words)] ?? '', 0, 1) .
                                                substr($words[array_key_last($words)]  ?? '', 0, 1)
                                            );
                                            $palette  = ['#4361ee','#3a86ff','#06d6a0','#8338ec','#fb5607','#e07a5f','#3d405b','#2d6a4f'];
                                            $bgColor  = $palette[abs(crc32($p->patient_name)) % count($palette)];
                                        @endphp
                                        <tr>
                                            <td class="align-middle">{{ $loop->iteration + ($patients->currentPage() - 1) * $patients->perPage() }}</td>

                                            {{-- Patient --}}
                                            <td class="align-middle">
                                                <div class="d-flex align-items-center gap-2">
                                                    @if ($p->image)
                                                        <img src="{{ asset('storage/' . $p->image) }}" width="40" height="40"
                                                            class="rounded-2 flex-shrink-0" style="object-fit:cover;">
                                                    @else
                                                        <div class="rounded-2 flex-shrink-0 d-flex align-items-center justify-content-center text-white fw-bold"
                                                            style="width:40px;height:40px;background:{{ $bgColor }};font-size:13px;letter-spacing:.5px">
                                                            {{ $initials }}
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <div class="fw-semibold lh-sm">{{ $p->patient_name }}</div>
                                                        <div class="text-muted" style="font-size:11.5px;line-height:1.6">
                                                            {{ $p->mrn }}{{ $p->health_card_no ? ' · ' . $p->health_card_no : '' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            {{-- Contact --}}
                                            <td class="align-middle">
                                                @if ($p->mobileno)
                                                    <div><i class="fa-solid fa-phone fa-xs text-muted me-1"></i>{{ $p->mobileno }}</div>
                                                @endif
                                                @if ($p->email)
                                                    <div class="text-muted small mt-1"><i class="fa-solid fa-envelope fa-xs me-1"></i>{{ $p->email }}</div>
                                                @endif
                                            </td>

                                            {{-- Demographics --}}
                                            <td class="align-middle">
                                                <div class="d-flex flex-wrap gap-1 align-items-center">
                                                    @if ($p->gender)
                                                        @php $g = strtolower($p->gender); @endphp
                                                        @if ($g === 'male')
                                                            <span class="badge bg-info"><i class="fa-solid fa-mars me-1"></i>Male</span>
                                                        @elseif ($g === 'female')
                                                            <span class="badge" style="background:#c2185b"><i class="fa-solid fa-venus me-1"></i>Female</span>
                                                        @else
                                                            <span class="badge bg-secondary">{{ $p->gender }}</span>
                                                        @endif
                                                    @endif
                                                    @if ($p->blood_group)
                                                        <span class="badge bg-danger">{{ $p->blood_group }}</span>
                                                    @endif
                                                </div>
                                                @if ($p->dob)
                                                    <div class="text-muted mt-1" style="font-size:11.5px">
                                                        {{ $p->dob->format('d M Y') }} &middot; {{ $p->dob->age }}y
                                                    </div>
                                                @endif
                                            </td>

                                            {{-- Status --}}
                                            <td class="align-middle">
                                                <div class="d-flex flex-wrap gap-1">
                                                    @if ($p->is_active)
                                                        <span class="badge bg-success">Active</span>
                                                    @else
                                                        <span class="badge bg-secondary">Inactive</span>
                                                    @endif
                                                    @if ($p->is_ipd)
                                                        <span class="badge bg-warning text-dark">IPD</span>
                                                    @endif
                                                    @if ($p->is_dead)
                                                        <span class="badge bg-dark">Deceased</span>
                                                    @endif
                                                </div>
                                            </td>

                                            {{-- Action --}}
                                            <td class="align-middle">
                                                <div class="d-flex flex-wrap gap-1">
                                                    <a class="btn btn-sm btn-info" title="View" href="{{ route('patients.show', $p) }}">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </a>
                                                    <a class="btn btn-sm btn-warning" title="Edit" href="{{ route('patients.edit', $p) }}">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </a>
                                                    <a class="btn btn-sm btn-success" title="Health Card" href="{{ route('health-card.show', $p) }}" target="_blank">
                                                        <i class="fa-solid fa-id-card"></i>
                                                    </a>
                                                    <form method="POST" action="{{ route('patients.destroy', $p) }}"
                                                        onsubmit="return confirm('Delete this patient?')" class="m-0">
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
                                            <td colspan="6" class="text-center text-muted py-4">No patients found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        @if ($patients->hasPages())
                            <div class="d-flex justify-content-end py-3">
                                {{ $patients->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
