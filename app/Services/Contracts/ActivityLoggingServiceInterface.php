<?php

namespace App\Services\Contracts;

use Illuminate\Database\Eloquent\Model;

interface ActivityLoggingServiceInterface
{
    /**
     * Log user login activity
     *
     * @param Model $user
     * @param array $properties
     * @return void
     */
    public function logLogin(Model $user, array $properties = []): void;

    /**
     * Log user logout activity
     *
     * @param Model $user
     * @param array $properties
     * @return void
     */
    public function logLogout(Model $user, array $properties = []): void;

    /**
     * Log a custom activity
     *
     * @param Model $user
     * @param string $description
     * @param array $properties
     * @return void
     */
    public function logActivity(Model $user, string $description, array $properties = []): void;
}
