<?php

namespace App\Application\User\Actions;

use App\Application\User\DTO\ForgotPasswordDTO;
use App\Domain\User\Services\AuthenticationService;

class ForgotPasswordAction
{
    public function __construct(
        private readonly AuthenticationService $authService
    ) {}

    public function execute(ForgotPasswordDTO $dto): string
    {
        return $this->authService->sendPasswordResetLink($dto->email);
    }
}
