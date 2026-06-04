<?php

namespace App\Services;

use App\Services\Contracts\ActivityLoggingServiceInterface;
use Illuminate\Database\Eloquent\Model;

class ActivityLoggingService implements ActivityLoggingServiceInterface
{
    /**
     * Log user login activity
     *
     * @param Model $user
     * @param array $properties
     * @return void
     */
    public function logLogin(Model $user, array $properties = []): void
    {
        activity()
            ->performedOn($user)
            ->withProperties($properties)
            ->log('User logged in');
    }

    /**
     * Log user logout activity
     *
     * @param Model $user
     * @param array $properties
     * @return void
     */
    public function logLogout(Model $user, array $properties = []): void
    {
        activity()
            ->performedOn($user)
            ->withProperties($properties)
            ->log('User logged out');
    }

    /**
     * Log a custom activity
     *
     * @param Model $user
     * @param string $description
     * @param array $properties
     * @return void
     */
    public function logActivity(Model $user, string $description, array $properties = []): void
    {
        activity()
            ->performedOn($user)
            ->withProperties($properties)
            ->log($description);
    }
}
