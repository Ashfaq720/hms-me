<?php

namespace App\Application\User\UseCases;

use App\Domain\User\Services\AuthenticationService;

class LogoutUserUseCase
{
    public function __construct(
        private readonly AuthenticationService $authService
    ) {}

    public function execute(): void
    {
        $this->authService->logout();
    }
}
