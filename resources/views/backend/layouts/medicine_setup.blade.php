<div class="card border-0 shadow-sm rounded-3 sticky-top" style="top: 30px;">
    <div class="card-header bg-white border-0 pb-2 pt-3 px-3">
        <h6 class="mb-1 fw-semibold text-dark">Medicine Setup</h6>
        <p class="mb-0 text-muted small">Manage medicine related master data</p>
    </div>

    <div class="card-body p-2">
        <div class="list-group list-group-flush sidebar-menu">

            <a href="{{ route('admin.medicine-categories.index') }}"
                class="list-group-item list-group-item-action d-flex align-items-center rounded-3 mb-1
               {{ request()->routeIs('admin.medicine-categories.*') ? 'active' : '' }}">
                <span class="menu-icon">
                    <i class="bi bi-folder2-open"></i>
                </span>
                <span class="flex-grow-1">Category of medicine</span>
                <i class="bi bi-chevron-right small"></i>
            </a>
            <a href="{{ route('admin.suppliers.index') }}"
                class="list-group-item list-group-item-action d-flex align-items-center rounded-3 mb-1
               {{ request()->routeIs('admin.suppliers.*') ? 'active' : '' }}">
                <span class="menu-icon">
                    <i class="bi bi-truck"></i>
                </span>
                <span class="flex-grow-1">The supplier</span>
                <i class="bi bi-chevron-right small"></i>
            </a>

            <a href="{{ route('admin.medicine-units.index') }}"
                class="list-group-item list-group-item-action d-flex align-items-center rounded-3 mb-1
               {{ request()->routeIs('admin.medicine-units.*') ? 'active' : '' }}">
                <span class="menu-icon">
                    <i class="bi bi-bounding-box"></i>
                </span>
                <span class="flex-grow-1">Unite Type</span>
                <i class="bi bi-chevron-right small"></i>
            </a>

            <a href="{{ route('admin.companies.index') }}"
                class="list-group-item list-group-item-action d-flex align-items-center rounded-3 mb-1
               {{ request()->routeIs('admin.companies.*') ? 'active' : '' }}">
                <span class="menu-icon">
                    <i class="bi bi-building"></i>
                </span>
                <span class="flex-grow-1">Company</span>
                <i class="bi bi-chevron-right small"></i>
            </a>

            <a href="{{ route('admin.medical-groups.index') }}"
                class="list-group-item list-group-item-action d-flex align-items-center rounded-3
               {{ request()->routeIs('admin.medical-groups.*') ? 'active' : '' }}">
                <span class="menu-icon">
                  <i class="bi bi-diagram-3"></i>
                </span>
                <span class="flex-grow-1">Medical Group</span>
                <i class="bi bi-chevron-right small"></i>
            </a>

            <a href="{{ route('admin.medicine-generics.index') }}"
                class="list-group-item list-group-item-action d-flex align-items-center rounded-3
               {{ request()->routeIs('admin.medicine-generics.*') ? 'active' : '' }}">
                <span class="menu-icon">
                    <i class="bi bi-capsule-pill"></i> 
                </span>
                <span class="flex-grow-1">Medicine Generic</span>
                <i class="bi bi-chevron-right small"></i>
            </a>

        </div>
    </div>
</div>
