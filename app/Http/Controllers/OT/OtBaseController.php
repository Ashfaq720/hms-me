<?php

namespace App\Http\Controllers\OT;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

abstract class OtBaseController extends Controller
{
    protected function gate(string $permission): void
    {
        $user = Auth::user();

        if (! $user) {
            abort(403, 'Unauthenticated.');
        }

        $isSuperAdmin = $user->roles->pluck('name')
            ->map(fn ($r) => strtolower($r))
            ->contains('super admin');

        if ($isSuperAdmin) {
            return;
        }

        if (! $user->can($permission)) {
            abort(403, "Missing permission: {$permission}");
        }
    }
}
