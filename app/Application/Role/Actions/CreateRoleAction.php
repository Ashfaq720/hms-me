<?php

namespace App\Application\Role\Actions;

use App\Application\Role\DTO\CreateRoleDTO;
use App\Domain\Role\Services\RoleService;
use App\Domain\Role\Entities\RoleEntity;

class CreateRoleAction
{
    public function __construct(
        private readonly RoleService $roleService
    ) {}

    public function execute(CreateRoleDTO $dto): RoleEntity
    {
        try {
            return $this->roleService->create($dto->toArray());
        } catch (\Exception $e) {
            throw new \Exception("Failed to create role '{$dto->name}': {$e->getMessage()}");
        }
    }
}
