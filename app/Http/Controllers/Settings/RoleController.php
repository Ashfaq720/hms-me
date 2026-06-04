<?php

namespace App\Http\Controllers\Settings;

use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Domain\Role\Entities\RoleEntity;
use App\Models\Module;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Spatie\Permission\Models\Permission;
use App\Domain\Role\Services\RoleService;
use App\Application\Role\DTO\CreateRoleDTO;
use App\Application\Role\DTO\UpdateRoleDTO;
use App\Application\Role\Actions\CreateRoleAction;
use App\Application\Role\Actions\UpdateRoleAction;

class RoleController extends Controller
{
    public function __construct(
        private readonly RoleService $roleService,
        private readonly CreateRoleAction $createRoleAction,
        private readonly UpdateRoleAction $updateRoleAction
    ) {
        $this->middleware(['auth', 'can:role_access']);
    }

    public function index(): View
    {
        try {
            $roles = $this->roleService->getActiveRoles();

            return view('backend.roles.index', compact('roles'));
        } catch (\Exception $e) {
            return view('backend.roles.index')
                ->with('toast_error', 'Unable to load roles. Please try again.');
        }
    }

    public function create(): View|RedirectResponse
    {
        try {
            $modules = Module::with('permissions')->get();

            return view('backend.roles.create', compact('modules'));
        } catch (\Exception $e) {
            return redirect()->route('roles.index')
                ->with('toast_error', 'Unable to load role creation page. Please try again.');
        }
    }

    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'display_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
            'priority' => 'nullable|integer|min:0|max:100',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        DB::beginTransaction();

        try {
            $dto = CreateRoleDTO::fromRequest($validatedData);
            $role = $this->createRoleAction->execute($dto);

            DB::commit();

            return redirect()->route('roles.index')
                ->with('toast_success', "Role '{$role->name}' created successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('toast_error', $e->getMessage());
        }
    }

    public function edit(Role $role): View|RedirectResponse
    {
        try {
            $roleEntity = $this->roleService->findWithPermissions($role->id);
            $eloquentRole = $role->load('permissions');
            $modules = Module::with('permissions')->get();

            return view('backend.roles.edit', compact('roleEntity', 'eloquentRole', 'modules'));
        } catch (\Exception $e) {
            return redirect()->route('roles.index')
                ->with('toast_error', 'Unable to load role edit page. Please try again.');
        }
    }

    public function show(Role $role): View|RedirectResponse
    {
        try {
            $role->load('permissions');

            return view('backend.roles.show', compact('role'));
        } catch (\Exception $e) {
            return redirect()->route('roles.index')
                ->with('toast_error', 'Unable to load role details. Please try again.');
        }
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:roles,name,' . $role->id,
            'display_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
            'priority' => 'nullable|integer|min:0|max:100',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $validatedData['permissions'] = $validatedData['permissions'] ?? [];
        $validatedData['is_active'] = $request->boolean('is_active');

        DB::beginTransaction();

        try {
            $dto = UpdateRoleDTO::fromRequest($validatedData);
            $roleEntity = RoleEntity::fromArray($role->toArray());
            $updatedRole = $this->updateRoleAction->execute($roleEntity, $dto);

            DB::commit();

            return redirect()->route('roles.index')
                ->with('toast_success', "Role '{$updatedRole->name}' updated successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('toast_error', $e->getMessage());
        }
    }

    public function destroy(Role $role): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $roleName = $role->name;
            $roleEntity = RoleEntity::fromArray($role->toArray());
            $deleted = $this->roleService->delete($roleEntity);

            if (!$deleted) {
                throw new \Exception("Role could not be deleted.");
            }

            DB::commit();

            return redirect()->route('roles.index')
                ->with('toast_success', "Role '{$roleName}' deleted successfully.");
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('toast_error', $e->getMessage());
        }
    }

    public function getPermission(int $id): JsonResponse
    {
        try {
            $role = $this->roleService->findWithPermissions($id);

            return response()->json([
                'success' => true,
                'permissions' => $role->permissions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve role permissions.'
            ], 500);
        }
    }

    public function updatePermission(Request $request, Role $role): JsonResponse
    {

        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        DB::beginTransaction();

        try {
            $updatedRole = $this->roleService->syncPermissions($role, $request->input('permissions', []));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Permissions updated successfully',
                'role' => $updatedRole->load('permissions')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getRoleName(): JsonResponse
    {
        try {
            $roles = $this->roleService->getActiveRoles();
            $roleNames = $roles->pluck('name');

            return response()->json([
                'success' => true,
                'roles' => $roleNames
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve role names.'
            ], 500);
        }
    }
}
