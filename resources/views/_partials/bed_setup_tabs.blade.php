{{-- Shared tab strip for Floor / Room / Bed Group / Bed Type / Bed.
     Replaces the sticky sidebar with a horizontal pill nav so every
     bed-master page gets a consistent full-width layout. --}}
<ul class="nav nav-pills mb-3 flex-wrap gap-1">
    <li class="nav-item">
        <a class="nav-link {{ request()->is('floors*') ? 'active' : '' }}"
           href="{{ route('floors.index') }}">
            <i class="bi bi-layers me-1"></i> Floors
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->is('rooms*') ? 'active' : '' }}"
           href="{{ route('rooms.index') }}">
            <i class="bi bi-door-open me-1"></i> Rooms
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->is('bed-groups*') ? 'active' : '' }}"
           href="{{ route('bed-groups.index') }}">
            <i class="bi bi-grid me-1"></i> Bed Groups
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->is('bed-types*') ? 'active' : '' }}"
           href="{{ route('bed-types.index') }}">
            <i class="bi bi-ui-checks me-1"></i> Bed Types
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->is('beds*') ? 'active' : '' }}"
           href="{{ route('beds.index') }}">
            <i class="bi bi-hospital me-1"></i> Beds
        </a>
    </li>
</ul>
