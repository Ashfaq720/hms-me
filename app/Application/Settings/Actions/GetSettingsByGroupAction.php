<?php

namespace App\Application\Settings\Actions;

use App\Domain\Settings\Services\SettingService;
use Illuminate\Support\Collection;

class GetSettingsByGroupAction
{
    public function __construct(
        private readonly SettingService $settingService
    ) {}

    public function execute(string $group): Collection
    {
        try {
            return $this->settingService->getByGroup($group);
        } catch (\Exception $e) {
            throw new \Exception("Failed to retrieve settings for group '{$group}': {$e->getMessage()}");
        }
    }
}
