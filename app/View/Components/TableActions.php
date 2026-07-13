<?php

namespace App\View\Components;

use Illuminate\View\Component;

class TableActions extends Component
{
    public $viewType;
    public $editType;
    public $assignType;
    public $viewRoute;
    public $editRoute;
    public $deleteRoute;
    public $assignPermissionRoute;
    public $modelId;
    public $modelSlug;
    public $viewPermission;
    public $editPermission;
    public $deletePermission;
    public $assignPermission;
    public $permissions;
    public $customActions;

    /**
     * Create a new component instance.
     */
    public function __construct(
        $viewType = 'modal',
        $editType = 'modal',
        $assignType = 'modal',
        $viewRoute = null,
        $editRoute = null,
        $deleteRoute = null,
        $assignPermissionRoute = null,
        $modelId = null,
        $modelSlug = null,
        $viewPermission = null,
        $editPermission = null,
        $deletePermission = null,
        $assignPermission = null,
        $permissions = [],
        $customActions = []
    ) {
        $this->viewType = $viewType;
        $this->editType = $editType;
        $this->assignType = $assignType;
        $this->viewRoute = $viewRoute;
        $this->editRoute = $editRoute;
        $this->deleteRoute = $deleteRoute;
        $this->assignPermissionRoute = $assignPermissionRoute;
        $this->modelId = $modelId;
        $this->modelSlug = $modelSlug;
        $this->viewPermission = $viewPermission;
        $this->editPermission = $editPermission;
        $this->deletePermission = $deletePermission;
        $this->assignPermission = $assignPermission;
        $this->permissions = $permissions;
        $this->customActions = $customActions;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('backend.components.table-actions');
    }
}
