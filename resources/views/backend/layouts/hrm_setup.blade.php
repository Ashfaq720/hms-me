
<div class="card sticky-top shadow-sm h-auto" style="top:30px; height: fit-content;">
    <div class="list-group list-group-flush mb-0">

        {{-- Department --}}
        <a href="{{ route('departments.index') }}"
            class="list-group-item list-group-item-action d-flex align-items-center
           {{ request()->is('departments*') ? 'active' : '' }}">
            <i class="bi bi-layers me-2"></i>
            <span>Department</span>
            <i class="bi bi-chevron-right ms-auto"></i>
        </a>

        {{-- Designation --}}
        <a href="{{ route('designations.index') }}"
            class="list-group-item list-group-item-action d-flex align-items-center
           {{ request()->is('designations*') ? 'active' : '' }}">
            <i class="bi bi-layers me-2"></i>
            <span>Designation</span>
            <i class="bi bi-chevron-right ms-auto"></i>
        </a>

        {{-- Specialist --}}
        <a href="{{ route('specialists.index') }}"
            class="list-group-item list-group-item-action d-flex align-items-center
           {{ request()->is('specialists*') ? 'active' : '' }}">
            <i class="bi bi-layers me-2"></i>
            <span>Specialists</span>
            <i class="bi bi-chevron-right ms-auto"></i>
        </a>


    </div>
</div>
