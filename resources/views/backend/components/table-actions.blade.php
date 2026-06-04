@php
    $canView = $viewPermission ? (auth()->user()->hasRole('Super Admin') || auth()->user()->hasPermissionTo($viewPermission)) : false;
    $canEdit = $editPermission ? (auth()->user()->hasRole('Super Admin') || auth()->user()->hasPermissionTo($editPermission)) : false;
    $canDelete = $deletePermission ? (auth()->user()->hasRole('Super Admin') || auth()->user()->hasPermissionTo($deletePermission)) : false;
    $canAssignPermission = $assignPermission ? (auth()->user()->hasRole('Super Admin') || auth()->user()->hasPermissionTo($assignPermission)) : false;
@endphp

<div class="dropdown">
    <button class="btn btn-white btn-sm dropdown-toggle" type="button"
        data-bs-toggle="dropdown">
        <i class="fas fa-ellipsis-v"></i>
    </button>
    
    <ul class="dropdown-menu dropdown-menu-end">
        @if ($assignPermissionRoute && $canAssignPermission)
            @if ($assignType === 'modal')
                <li><a class="dropdown-item open-assign-permission-modal" href="#"
                    data-url="{{ route($assignPermissionRoute, $modelId ?? $modelSlug) }}"
                    data-target="#global-assign-permission-modal">
                        <i class="fas fa-lock me-2"></i> Assign Permissions
                    </a></li>
            @else
                <li><a class="dropdown-item" href="{{ route($assignPermissionRoute, $modelId ?? $modelSlug) }}">
                        <i class="fas fa-lock me-2"></i> Assign Permissions
                    </a></li>
            @endif
        @endif

        @if ($viewRoute && $canView)
            @if ($viewType === 'modal')
                <li><a class="dropdown-item open-view-modal" href="#"
                    data-url="{{ route($viewRoute, $modelId ?? $modelSlug) }}"
                    data-target="#global-view-modal">
                        <i class="fas fa-eye me-2"></i> View
                    </a></li>
            @else
                <li><a class="dropdown-item" href="{{ route($viewRoute, $modelId ?? $modelSlug) }}">
                        <i class="fas fa-eye me-2"></i> View
                    </a></li>
            @endif
        @endif

        @if ($editRoute && $canEdit)
            @if ($editType === 'modal')
                <li><a class="dropdown-item open-edit-modal" href="#"
                    data-url="{{ route($editRoute, $modelId) }}"
                    data-target="#global-edit-modal">
                        <i class="fas fa-edit me-2"></i> Edit
                    </a></li>
            @else
                <li><a class="dropdown-item" href="{{ route($editRoute, $modelId) }}">
                        <i class="fas fa-edit me-2"></i> Edit
                    </a></li>
            @endif
        @endif

        @if ($deleteRoute && $canDelete)
            <li><a class="dropdown-item delete_modal" href="#" data-bs-toggle="modal"
                data-url="{{ route($deleteRoute, $modelId) }}" data-id="{{ $modelId }}"
                data-title="Delete Item" data-bs-target="#delete_modal">
                    <i class="fas fa-trash me-2"></i> Delete
                </a></li>
        @endif

        @foreach($customActions as $action)
            <li><a class="dropdown-item {{ $action['class'] ?? '' }}" href="{{ $action['url'] ?? '#' }}"
                @if(isset($action['onclick'])) onclick="{{ $action['onclick'] }}" @endif
                @if(isset($action['data'])) @foreach($action['data'] as $key => $value) data-{{ $key }}="{{ $value }}" @endforeach @endif>
                    <i class="{{ $action['icon'] ?? 'fas fa-circle' }} me-2"></i> {{ $action['label'] }}
                </a></li>
        @endforeach
    </ul>
</div>