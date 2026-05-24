<div class="card sticky-top shadow-sm h-auto" style="top:30px; height: fit-content;">
    <div class="card-header bg-primary bg-opacity-10 border-0 py-2 px-3">
        <small class="text-primary fw-bold"><i class="bi bi-hospital"></i> BED & ROOM MANAGEMENT</small>
    </div>
    <div class="list-group list-group-flush mb-0">

        {{-- Floor --}}
        <a href="{{ route('floors.index') }}"
            class="list-group-item list-group-item-action d-flex align-items-center
           {{ request()->is('floors*') ? 'active' : '' }}">
            <i class="bi bi-layers me-2 text-info"></i>
            <span>Floor / Building</span>
            <i class="bi bi-chevron-right ms-auto small"></i>
        </a>

        {{-- Bed Group / Ward --}}
        <a href="{{ route('bed-groups.index') }}"
            class="list-group-item list-group-item-action d-flex align-items-center
           {{ request()->is('bed-groups*') ? 'active' : '' }}">
            <i class="bi bi-grid me-2 text-warning"></i>
            <span>Ward / Bed Group</span>
            <i class="bi bi-chevron-right ms-auto small"></i>
        </a>

        {{-- Room --}}
        <a href="{{ route('rooms.index') }}"
            class="list-group-item list-group-item-action d-flex align-items-center
           {{ request()->is('rooms*') ? 'active' : '' }}">
            <i class="bi bi-door-closed me-2 text-success"></i>
            <span>Room</span>
            <span class="badge bg-success bg-opacity-15 text-success ms-auto small">NEW</span>
        </a>

        {{-- Bed Type --}}
        <a href="{{ route('bed-types.index') }}"
           class="list-group-item list-group-item-action d-flex align-items-center
           {{ request()->is('bed-types*') ? 'active' : '' }}">
            <i class="bi bi-ui-checks me-2 text-secondary"></i>
            <span>Bed Type</span>
            <i class="bi bi-chevron-right ms-auto small"></i>
        </a>

        {{-- Bed --}}
        <a href="{{ route('beds.index') }}"
           class="list-group-item list-group-item-action d-flex align-items-center
           {{ request()->is('beds*') ? 'active' : '' }}">
            <i class="bi bi-hospital me-2 text-primary"></i>
            <span>Bed</span>
            <i class="bi bi-chevron-right ms-auto small"></i>
        </a>

    </div>
    <div class="card-footer bg-light border-0 py-2 px-3">
        <small class="text-muted d-block" style="font-size:11px;">
            <i class="bi bi-info-circle"></i> Hierarchy: Floor → Ward → Room → Bed
        </small>
    </div>
</div>
