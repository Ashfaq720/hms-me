<div class="card sticky-top shadow-sm h-auto" style="top:30px; height: fit-content;">
    <div class="list-group list-group-flush mb-0">

        {{-- Types --}}
        <a href="{{ route('lab-investigation-types.index') }}"
            class="list-group-item list-group-item-action d-flex align-items-center
           {{ request()->is('lab-investigation-types*') ? 'active' : '' }}">
            <i class="bi bi-tags me-2"></i>
            <span>Types</span>
            <i class="bi bi-chevron-right ms-auto"></i>
        </a>

        {{-- Categories --}}
        <a href="{{ route('lab-investigation-categories.index') }}"
            class="list-group-item list-group-item-action d-flex align-items-center
           {{ request()->is('lab-investigation-categories*') ? 'active' : '' }}">
            <i class="bi bi-grid me-2"></i>
            <span>Categories</span>
            <i class="bi bi-chevron-right ms-auto"></i>
        </a>

        {{-- Lab Investigations --}}
        <a href="{{ route('lab-investigations.index') }}"
            class="list-group-item list-group-item-action d-flex align-items-center
           {{ request()->is('lab-investigations*') ? 'active' : '' }}">
            <i class="bi bi-clipboard2-pulse me-2"></i>
            <span>Lab Investigations</span>
            <i class="bi bi-chevron-right ms-auto"></i>
        </a>

    </div>
</div>
