<?php

namespace App\Http\Controllers\Settings;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Domain\Role\Entities\RoleEntity;
use App\Domain\User\Entities\UserEntity;
use App\Domain\User\Services\UserService;
use App\Models\User;
use App\Models\Role;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {
        $this->middleware(['auth', 'can:user_access']);
    }

    public function index(): View
    {
        try {
            $users = User::with(['roles'])
                // ->whereNotIn('type', ['super_admin'])
                ->latest()
                ->get();
            
            return view('backend.users.index', compact('users'));
        } catch (\Exception $e) {
            return view('backend.users.index', ['users' => collect()])
                ->withErrors(['error' => 'Unable to load users. Please try again.']);
        }
    }

    public function create(): View|RedirectResponse
    {
        try {
            $roles = Role::active()->get();
            
            return view('backend.users.create', compact('roles'));
        } catch (\Exception $e) {
            dd( $e->getMessage());
            return redirect()->route('users.index')
                ->with('toast_error', 'Unable to load user creation page. Please try again.');
        }
    }

    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'status' => 'nullable|boolean',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        DB::beginTransaction();

        try {
            $userEntity = $this->userService->createUser($validatedData);

            if (!empty($validatedData['roles'])) {
                $roles = Role::whereIn('id', $validatedData['roles'])->get();
                // Fetch Eloquent model to sync roles since UserEntity doesn't support it
                $userModel = User::findOrFail($userEntity->id);
                if ($userModel) {
                    $userModel->syncRoles($roles);
                }
            }

            DB::commit();

            return redirect()->route('users.index')
                ->with('toast_success', "User '{$userEntity->name}' created successfully.");
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('toast_error', $e->getMessage());
        }
    }

    public function edit(User $user): View|RedirectResponse
    {
        try {
            $roles = Role::active()->get();
            $userRoles = $user->roles()->pluck('id')->toArray();

            return view('backend.users.edit', compact('user', 'roles', 'userRoles'));
        } catch (\Exception $e) {
            return redirect()->route('users.index')
                ->with('toast_error', 'Unable to load user edit page. Please try again.');
        }
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'status' => 'nullable|boolean',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        DB::beginTransaction();

        try {
            $userEntity = UserEntity::fromArray($user->toArray());
            $updatedUser = $this->userService->updateUser($userEntity, $validatedData);

            if (isset($validatedData['roles'])) {
                $roles = Role::whereIn('id', $validatedData['roles'])->get();
                // Use the Eloquent model ($user) to sync roles, not the Entity ($updatedUser)
                $user->syncRoles($roles);
            }

            DB::commit();

            return redirect()->route('users.index')
                ->with('toast_success', "User '{$updatedUser->name}' updated successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('toast_error', $e->getMessage());
        }
    }

    public function destroy(User $user): RedirectResponse
    {
        DB::beginTransaction();

        try {
            if ($user->id === auth()->id()) {
                throw new \Exception("You cannot delete your own account.");
            }

            $userName = $user->name;
            $userEntity = UserEntity::fromArray($user->toArray());
            $deleted = $this->userService->deleteUser($userEntity);

            if (!$deleted) {
                throw new \Exception("User could not be deleted.");
            }

            DB::commit();

            Log::info('User deleted successfully via controller', [
                'user_id' => $user->id,
                'user_name' => $userName,
                'deleted_by' => auth()->id(),
            ]);

            return redirect()->route('users.index')
                ->with('toast_success', "User '{$userName}' deleted successfully.");
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to delete user via controller', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);

            return redirect()->back()
                ->with('toast_error', $e->getMessage());
        }
    }

    public function show(User $user): View|RedirectResponse
    {
        try {
            $user->load(['roles.permissions']);
            
            return view('backend.users.show', compact('user'));
        } catch (\Exception $e) {
            Log::error('Failed to load user details page', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('users.index')
                ->with('toast_error', 'Unable to load user details. Please try again.');
        }
    }
}
