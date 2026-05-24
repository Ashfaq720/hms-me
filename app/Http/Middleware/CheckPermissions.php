<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermissions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $permission = null): Response
    {
        $user = Auth::user();

        // Patient portal uses a separate 'patient' guard — skip RBAC checks.
        if ($request->is('portal*')) {
            return $next($request);
        }

        // Allow everything for Super Admin (case-insensitive match against role name)
        if ($user && method_exists($user, 'roles')) {
            $roles = $user->roles;
            if ($roles) {
                $roleNames = $roles->pluck('name')->map(fn($r) => strtolower($r))->toArray();
                if (in_array('super admin', $roleNames, true)) {
                    return $next($request); // Allow access
                }
            }
        }

        // If middleware wasn't provided a specific permission just pass through
        if (is_null($permission)) {
            return $next($request);
        }

        // If user not authenticated, deny
        if (! $user) {
            abort(403, 'Unauthenticated: Access denied.');
        }

        // Validate permission exists in the system
        if (!\Spatie\Permission\Models\Permission::where('name', $permission)->exists()) {
            abort(403, "Permission '{$permission}' does not exist.");
        }

        // Check if the user has the requested permission (via role or direct)
        if (! $user->can($permission)) {
            abort(403, 'Unauthorized access: you do not have the required permission.');
        }

        return $next($request);
    }
}
