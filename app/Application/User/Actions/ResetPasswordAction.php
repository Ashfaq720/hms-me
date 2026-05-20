<?php

namespace App\Application\User\Actions;

use App\Application\User\DTO\ResetPasswordDTO;
use App\Domain\User\Services\AuthenticationService;

class ResetPasswordAction
{
    public function __construct(
        private readonly AuthenticationService $authService
    ) {}

    public function execute(ResetPasswordDTO $dto): bool
    {
        return $this->authService->resetPassword(
            $dto->email,
            $dto->password,
            $dto->token
        );
    }
}
