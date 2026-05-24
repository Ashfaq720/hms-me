<div class="card sticky-top shadow-sm h-auto" style="top:30px; height: fit-content;">
    <div class="list-group list-group-flush mb-0">

        {{-- Operation Type --}}
        <a href="{{ route('operation-types.index') }}"
            class="list-group-item list-group-item-action d-flex align-items-center
           {{ request()->is('operation-types*') ? 'active' : '' }}">
            <i class="bi bi-tags me-2"></i>
            <span>Operation Type</span>
            <i class="bi bi-chevron-right ms-auto"></i>
        </a>

        {{-- Operation --}}
        <a href="{{ route('operations.index') }}"
            class="list-group-item list-group-item-action d-flex align-items-center
           {{ request()->is('operations*') ? 'active' : '' }}">
            <i class="bi bi-clipboard2-pulse me-2"></i>
            <span>Operation</span>
            <i class="bi bi-chevron-right ms-auto"></i>
        </a>

        {{-- Operation Procedure --}}
        <a href="{{ route('operation-procedures.index') }}"
           class="list-group-item list-group-item-action d-flex align-items-center
           {{ request()->is('operation-procedures*') ? 'active' : '' }}">
            <i class="bi bi-list-check me-2"></i>
            <span>Operation Procedure</span>
            <i class="bi bi-chevron-right ms-auto"></i>
        </a>

        {{-- Operation Theatre --}}
        <a href="{{ route('operation-theatres.index') }}"
           class="list-group-item list-group-item-action d-flex align-items-center
           {{ request()->is('operation-theatres*') ? 'active' : '' }}">
            <i class="bi bi-building me-2"></i>
            <span>Operation Theatre</span>
            <i class="bi bi-chevron-right ms-auto"></i>
        </a>

    </div>
</div>
