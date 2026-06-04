@extends('backend.layouts.master')

@section('title', 'Rooms')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-3 col-md-4 mb-3">
            @include('backend.layouts.bed_setup')
        </div>

        <div class="col-lg-9 col-md-8">

            {{-- Header --}}
            <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between mb-3">
                <div>
                    <h4 class="mb-0"><i class="bi bi-door-closed text-success"></i> Rooms</h4>
                    <small class="text-muted">Floor → Ward → <strong>Room</strong> → Bed · Class · Capacity · Rent · Amenities</small>
                </div>
                <div class="d-flex gap-2">
                    <div class="btn-group btn-group-sm" role="group" aria-label="View toggle">
                        <a href="{{ request()->fullUrlWithQuery(['view' => 'table']) }}"
                            class="btn btn-outline-secondary {{ request('view', 'table') === 'table' ? 'active' : '' }}">
                            <i class="bi bi-table"></i> Table
                        </a>
                        <a href="{{ request()->fullUrlWithQuery(['view' => 'grid']) }}"
                            class="btn btn-outline-secondary {{ request('view') === 'grid' ? 'active' : '' }}">
                            <i class="bi bi-grid-3x3-gap"></i> Grid
                        </a>
                        <a href="{{ request()->fullUrlWithQuery(['view' => 'floor']) }}"
                            class="btn btn-outline-secondary {{ request('view') === 'floor' ? 'active' : '' }}">
                            <i class="bi bi-layers"></i> By Floor
                        </a>
                    </div>
                    <a href="{{ route('rooms.create') }}" class="btn btn-success btn-sm">
                        <i class="bi bi-plus-lg"></i> New Room
                    </a>
                </div>
            </div>

            @if (session('success')) <div class="alert alert-success py-2">{{ session('success') }}</div> @endif
            @if (session('error'))   <div class="alert alert-danger py-2">{{ session('error') }}</div> @endif

            {{-- KPI tiles --}}
            @php
                $allRooms   = \App\Models\Room::all();
                $totalRooms = $allRooms->count();
                $totalCap   = $allRooms->sum('capacity');
                $totalBeds  = \App\Models\Bed::whereNotNull('room_id')->count();
                $occupied   = \App\Models\Bed::whereNotNull('room_id')->where('status', 'occupied')->count();
                $available  = \App\Models\Bed::whereNotNull('room_id')->where('status', 'available')->count();
                $occRate    = $totalBeds > 0 ? round($occupied * 100 / $totalBeds, 1) : 0;
                $revenue    = $allRooms->sum(fn ($r) => $r->room_rent * $r->capacity);
                $classColours = [
                    'general'       => 'secondary',
                    'semi_private'  => 'info',
                    'private_cabin' => 'primary',
                    'deluxe'        => 'warning',
                    'vvip_suite'    => 'danger',
                    'icu'           => 'danger',
                    'ccu'           => 'warning',
                    'nicu'          => 'info',
                    'isolation'     => 'dark',
                    'recovery'      => 'success',
                    'maternity'     => 'success',
                ];
                $classIcons = [
                    'general'       => 'people',
                    'semi_private'  => 'people-fill',
                    'private_cabin' => 'door-closed-fill',
                    'deluxe'        => 'gem',
                    'vvip_suite'    => 'stars',
                    'icu'           => 'heart-pulse',
                    'ccu'           => 'heart',
                    'nicu'          => 'emoji-smile',
                    'isolation'     => 'shield-fill-exclamation',
                    'recovery'      => 'bed',
                    'maternity'     => 'gift',
                ];
            @endphp
            <div class="row g-2 mb-3">
                <div class="col-md col-6">
                    <div class="card border-0 shadow-sm bg-primary bg-opacity-10">
                        <div class="card-body py-2 px-3">
                            <small class="text-primary"><i class="bi bi-door-closed"></i> Total Rooms</small>
                            <h4 class="mb-0">{{ $totalRooms }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md col-6">
                    <div class="card border-0 shadow-sm bg-info bg-opacity-10">
                        <div class="card-body py-2 px-3">
                            <small class="text-info"><i class="bi bi-hospital"></i> Bed Capacity</small>
                            <h4 class="mb-0">{{ $totalCap }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md col-6">
                    <div class="card border-0 shadow-sm bg-success bg-opacity-10">
                        <div class="card-body py-2 px-3">
                            <small class="text-success"><i class="bi bi-check-circle"></i> Available</small>
                            <h4 class="mb-0">{{ $available }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md col-6">
                    <div class="card border-0 shadow-sm bg-danger bg-opacity-10">
                        <div class="card-body py-2 px-3">
                            <small class="text-danger"><i class="bi bi-person-fill"></i> Occupied</small>
                            <h4 class="mb-0">{{ $occupied }} <small class="text-muted">({{ $occRate }}%)</small></h4>
                        </div>
                    </div>
                </div>
                <div class="col-md col-12">
                    <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
                        <div class="card-body py-2 px-3">
                            <small class="text-warning"><i class="bi bi-cash-stack"></i> Potential Daily Revenue</small>
                            <h4 class="mb-0">৳ {{ number_format($revenue, 0) }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filter chips --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body py-2 px-3">
                    <form method="GET" class="row g-2 align-items-end">
                        <input type="hidden" name="view" value="{{ request('view', 'table') }}">
                        <div class="col-md-3">
                            <label class="form-label small text-muted mb-1">🔍 Search</label>
                            <input name="q" value="{{ request('q') }}" placeholder="Room no / name" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted mb-1">Floor</label>
                            <select name="floor_id" class="form-select form-select-sm">
                                <option value="">All Floors</option>
                                @foreach ($floors as $f)
                                    <option value="{{ $f->id }}" @selected(request('floor_id') == $f->id)>{{ $f->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted mb-1">Ward</label>
                            <select name="bed_group_id" class="form-select form-select-sm">
                                <option value="">All Wards</option>
                                @foreach ($bedGroups as $g)
                                    <option value="{{ $g->id }}" @selected(request('bed_group_id') == $g->id)>{{ $g->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted mb-1">Class</label>
                            <select name="room_class" class="form-select form-select-sm">
                                <option value="">All Classes</option>
                                @foreach (\App\Models\Room::CLASSES as $code => $label)
                                    <option value="{{ $code }}" @selected(request('room_class') === $code)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 d-flex gap-2 pt-1">
                            <button class="btn btn-sm btn-primary"><i class="bi bi-search"></i> Filter</button>
                            @if (request()->hasAny(['q', 'floor_id', 'bed_group_id', 'room_class']))
                                <a href="{{ route('rooms.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg"></i> Clear</a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            {{-- ======== TABLE VIEW (default) ======== --}}
            @if (request('view', 'table') === 'table')
                <div class="card border-0 shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Room</th>
                                    <th>Class</th>
                                    <th>Floor / Ward</th>
                                    <th class="text-center">Cap</th>
                                    <th class="text-end">Daily Rent</th>
                                    <th>Amenities</th>
                                    <th class="text-center">Beds</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($rooms as $r)
                                    @php
                                        $colour = $classColours[$r->room_class] ?? 'secondary';
                                        $icon   = $classIcons[$r->room_class] ?? 'door-closed';
                                        $occBeds = $r->beds->where('status', 'occupied')->count();
                                        $availBeds = $r->beds->where('status', 'available')->count();
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <strong>{{ $r->room_no }}</strong>
                                            @if ($r->name)<br><small class="text-muted">{{ $r->name }}</small>@endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $colour }} bg-opacity-15 text-{{ $colour }}">
                                                <i class="bi bi-{{ $icon }}"></i> {{ \App\Models\Room::CLASSES[$r->room_class] ?? $r->room_class }}
                                            </span>
                                        </td>
                                        <td>
                                            <small>
                                                <i class="bi bi-layers text-info"></i> {{ optional($r->floor)->name ?? '—' }}<br>
                                                <i class="bi bi-grid text-warning"></i> {{ optional($r->bedGroup)->name ?? '—' }}
                                            </small>
                                        </td>
                                        <td class="text-center">{{ $r->capacity }}</td>
                                        <td class="text-end"><strong>৳ {{ number_format($r->room_rent, 0) }}</strong></td>
                                        <td>
                                            @foreach ($r->amenityList() as $a)
                                                <span class="badge bg-light text-dark border" style="font-size:10px;">{{ $a }}</span>
                                            @endforeach
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success bg-opacity-15 text-success" title="Available">{{ $availBeds }}</span>
                                            @if ($occBeds > 0)
                                                <span class="badge bg-danger bg-opacity-15 text-danger" title="Occupied">{{ $occBeds }}</span>
                                            @endif
                                            <small class="text-muted">/ {{ $r->beds->count() }}</small>
                                        </td>
                                        <td class="text-end text-nowrap">
                                            <a href="{{ route('rooms.edit', $r) }}" class="btn btn-sm btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                                            <form method="POST" action="{{ route('rooms.destroy', $r) }}" class="d-inline" onsubmit="return confirm('Delete room?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="9" class="text-center text-muted py-5"><i class="bi bi-door-open display-5"></i><p class="mt-2 mb-0">No rooms found.</p><a href="{{ route('rooms.create') }}" class="btn btn-sm btn-success mt-2"><i class="bi bi-plus-lg"></i> Add the first room</a></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($rooms->hasPages())
                        <div class="card-footer bg-white">{{ $rooms->links() }}</div>
                    @endif
                </div>

            {{-- ======== GRID VIEW (cards) ======== --}}
            @elseif (request('view') === 'grid')
                <div class="row g-3">
                    @forelse ($rooms as $r)
                        @php
                            $colour = $classColours[$r->room_class] ?? 'secondary';
                            $icon   = $classIcons[$r->room_class] ?? 'door-closed';
                            $occBeds = $r->beds->where('status', 'occupied')->count();
                            $availBeds = $r->beds->where('status', 'available')->count();
                        @endphp
                        <div class="col-md-4 col-lg-3">
                            <div class="card border-0 shadow-sm h-100" style="border-top:4px solid var(--bs-{{ $colour }}) !important;">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h5 class="mb-0">{{ $r->room_no }}</h5>
                                            <small class="text-muted">{{ optional($r->bedGroup)->name }}</small>
                                        </div>
                                        <span class="badge bg-{{ $colour }} bg-opacity-15 text-{{ $colour }}">
                                            <i class="bi bi-{{ $icon }}"></i> {{ \App\Models\Room::CLASSES[$r->room_class] ?? '—' }}
                                        </span>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-end mt-2 pb-2 border-bottom">
                                        <div>
                                            <small class="text-muted">Daily Rent</small>
                                            <h4 class="mb-0 text-{{ $colour }}">৳ {{ number_format($r->room_rent, 0) }}</h4>
                                        </div>
                                        <div class="text-end">
                                            <small class="text-muted">Capacity</small><br>
                                            <strong>{{ $r->capacity }} bed{{ $r->capacity > 1 ? 's' : '' }}</strong>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-wrap gap-1 my-2">
                                        @foreach ($r->amenityList() as $a)
                                            <span class="badge bg-light text-dark border" style="font-size:10px;">{{ $a }}</span>
                                        @endforeach
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center small">
                                        <div>
                                            <span class="badge bg-success bg-opacity-15 text-success">{{ $availBeds }} avail</span>
                                            @if ($occBeds > 0) <span class="badge bg-danger bg-opacity-15 text-danger">{{ $occBeds }} occ</span> @endif
                                        </div>
                                        <div>
                                            <a href="{{ route('rooms.edit', $r) }}" class="btn btn-xs btn-outline-warning"><i class="bi bi-pencil"></i></a>
                                            <form method="POST" action="{{ route('rooms.destroy', $r) }}" class="d-inline" onsubmit="return confirm('Delete?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-xs btn-outline-danger"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12"><div class="card border-0 shadow-sm p-5 text-center"><i class="bi bi-door-open display-3 text-muted"></i><h5 class="mt-3">No rooms found</h5></div></div>
                    @endforelse
                </div>
                @if ($rooms->hasPages())
                    <div class="mt-3">{{ $rooms->links() }}</div>
                @endif

            {{-- ======== FLOOR-GROUPED VIEW ======== --}}
            @else
                @php
                    $byFloor = $rooms->getCollection()->groupBy(fn ($r) => optional($r->floor)->name ?? 'Unassigned');
                @endphp
                @forelse ($byFloor as $floorName => $items)
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-primary bg-opacity-10 d-flex align-items-center justify-content-between">
                            <h6 class="mb-0 text-primary">
                                <i class="bi bi-layers"></i> {{ $floorName }}
                                <span class="badge bg-primary ms-1">{{ $items->count() }} rooms</span>
                            </h6>
                            <small class="text-muted">{{ $items->sum('capacity') }} beds</small>
                        </div>
                        <div class="card-body p-2">
                            <div class="row g-2">
                                @foreach ($items as $r)
                                    @php
                                        $colour = $classColours[$r->room_class] ?? 'secondary';
                                        $icon   = $classIcons[$r->room_class] ?? 'door-closed';
                                    @endphp
                                    <div class="col-md-3 col-sm-6">
                                        <a href="{{ route('rooms.edit', $r) }}" class="text-decoration-none">
                                            <div class="card border-{{ $colour }} h-100" style="border-width:2px;">
                                                <div class="card-body p-2">
                                                    <div class="d-flex justify-content-between">
                                                        <strong class="text-dark">{{ $r->room_no }}</strong>
                                                        <span class="badge bg-{{ $colour }} bg-opacity-15 text-{{ $colour }}"><i class="bi bi-{{ $icon }}"></i></span>
                                                    </div>
                                                    <small class="text-muted d-block">{{ optional($r->bedGroup)->name }}</small>
                                                    <div class="d-flex justify-content-between mt-1">
                                                        <small><strong>৳ {{ number_format($r->room_rent, 0) }}</strong>/day</small>
                                                        <small>{{ $r->capacity }} 🛏️</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="card border-0 shadow-sm p-5 text-center"><i class="bi bi-door-open display-3 text-muted"></i><h5 class="mt-3">No rooms found</h5></div>
                @endforelse
            @endif

        </div>
    </div>
</div>

@push('styles')
<style>
.btn-xs { padding: .15rem .4rem; font-size: .7rem; }
</style>
@endpush
@endsection
