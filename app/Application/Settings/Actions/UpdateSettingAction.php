<?php

namespace App\Application\Settings\Actions;

use App\Application\Settings\DTO\UpdateSettingDTO;
use App\Domain\Settings\Services\SettingService;
use App\Domain\Settings\Entities\SettingEntity;

class UpdateSettingAction
{
    public function __construct(
        private readonly SettingService $settingService
    ) {}

    public function execute(UpdateSettingDTO $dto): SettingEntity
    {
        try {
            $options = array_filter([
                'group' => $dto->group,
                'description' => $dto->description,
                'is_public' => $dto->isPublic,
                'is_active' => $dto->isActive,
            ], fn($value) => $value !== null);

            return $this->settingService->setValue(
                $dto->key,
                $dto->value,
                $dto->type,
                $options
            );
        } catch (\Exception $e) {
            throw new \Exception("Failed to update setting '{$dto->key}': {$e->getMessage()}");
        }
    }
}
