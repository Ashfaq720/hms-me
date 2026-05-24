<?php

namespace App\Application\Role\Actions;

use App\Application\Role\DTO\UpdateRoleDTO;
use App\Domain\Role\Services\RoleService;
use App\Domain\Role\Entities\RoleEntity;

class UpdateRoleAction
{
    public function __construct(
        private readonly RoleService $roleService
    ) {}

    public function execute(RoleEntity $role, UpdateRoleDTO $dto): RoleEntity
    {
        try {
            return $this->roleService->update($role, $dto->toArray());
        } catch (\Exception $e) {
            throw new \Exception("Failed to update role '{$role->name}': {$e->getMessage()}");
        }
    }
}
