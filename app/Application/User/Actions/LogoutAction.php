<?php

namespace App\Application\User\Actions;

use App\Application\User\UseCases\LogoutUserUseCase;

class LogoutAction
{
    public function __construct(
        private readonly LogoutUserUseCase $logoutUseCase
    ) {}

    public function execute(): void
    {
        $this->logoutUseCase->execute();
    }
}
