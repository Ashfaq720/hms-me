<div class="card border-0 shadow-sm rounded-3 sticky-top" style="top: 30px;">
    <div class="card-header bg-white border-0 pb-2 pt-3 px-3">
        <h6 class="mb-1 fw-semibold text-dark">Charges Setup</h6>
        <p class="mb-0 text-muted small">Manage charge related master data</p>
    </div>

    <div class="card-body p-2">
        <div class="list-group list-group-flush sidebar-menu">

            <a href="{{ route('admin.charges.index') }}"
               class="list-group-item list-group-item-action d-flex align-items-center rounded-3 mb-1
               {{ request()->routeIs('admin.charges.*') ? 'active' : '' }}">
                <span class="menu-icon">
                    <i class="bi bi-cash-stack"></i>
                </span>
                <span class="flex-grow-1">Charges</span>
                <i class="bi bi-chevron-right small"></i>
            </a>

            <a href="{{ route('admin.charge-categories.index') }}"
               class="list-group-item list-group-item-action d-flex align-items-center rounded-3 mb-1
               {{ request()->routeIs('admin.charge-categories.*') ? 'active' : '' }}">
                <span class="menu-icon">
                    <i class="bi bi-tags"></i>
                </span>
                <span class="flex-grow-1">Charge Category</span>
                <i class="bi bi-chevron-right small"></i>
            </a>

            <a href="{{ route('admin.charge-types.index') }}"
               class="list-group-item list-group-item-action d-flex align-items-center rounded-3 mb-1
               {{ request()->routeIs('admin.charge-types.*') ? 'active' : '' }}">
                <span class="menu-icon">
                    <i class="bi bi-diagram-3"></i>
                </span>
                <span class="flex-grow-1">Charge Type</span>
                <i class="bi bi-chevron-right small"></i>
            </a>

            <a href="{{ route('admin.tax-categories.index') }}"
               class="list-group-item list-group-item-action d-flex align-items-center rounded-3 mb-1
               {{ request()->routeIs('admin.tax-categories.*') ? 'active' : '' }}">
                <span class="menu-icon">
                    <i class="bi bi-receipt"></i>
                </span>
                <span class="flex-grow-1">Tax Category</span>
                <i class="bi bi-chevron-right small"></i>
            </a>

            <a href="{{ route('admin.unite-types.index') }}"
               class="list-group-item list-group-item-action d-flex align-items-center rounded-3
               {{ request()->routeIs('admin.unite-types.*') ? 'active' : '' }}">
                <span class="menu-icon">
                    <i class="bi bi-bounding-box"></i>
                </span>
                <span class="flex-grow-1">Unit Type</span>
                <i class="bi bi-chevron-right small"></i>
            </a>

        </div>
    </div>
</div>
