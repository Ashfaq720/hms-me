<?php

namespace App\Application\Settings\Actions;

use App\Application\Settings\DTO\UpdateMultipleSettingsDTO;
use App\Domain\Settings\Services\SettingService;
use Illuminate\Support\Collection;

class UpdateMultipleSettingsAction
{
    public function __construct(
        private readonly SettingService $settingService
    ) {}

    public function execute(UpdateMultipleSettingsDTO $dto): Collection
    {
        try {
            return $this->settingService->updateMultiple($dto->getSettings());
        } catch (\Exception $e) {
            throw new \Exception("Failed to update multiple settings: {$e->getMessage()}");
        }
    }
}
