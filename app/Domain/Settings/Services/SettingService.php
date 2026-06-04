<?php

namespace App\Domain\Settings\Services;

use App\Domain\Settings\Entities\SettingEntity;
use App\Domain\Settings\Repositories\SettingRepositoryInterface;
use App\Domain\Settings\ValueObjects\SettingValue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class SettingService
{
    protected SettingRepositoryInterface $settingRepository;

    public function __construct(SettingRepositoryInterface $settingRepository)
    {
        $this->settingRepository = $settingRepository;
    }

    /**
     * Get setting by key
     */
    public function getByKey(string $key): ?SettingEntity
    {
        try {
            return $this->settingRepository->findActiveByKey($key);
        } catch (\Exception $e) {
            throw new \Exception("Unable to retrieve setting: {$key}");
        }
    }

    /**
     * Get setting value by key
     */
    public function getValue(string $key, $default = null): mixed
    {
        try {
            $setting = $this->getByKey($key);

            if (!$setting) {
                return $default;
            }

            return $setting->getValue();
        } catch (\Exception $e) {
            return $default;
        }
    }

    /**
     * Set setting value
     */
    public function setValue(string $key, $value, string $type = 'string', array $options = []): SettingEntity
    {
        DB::beginTransaction();

        try {
            $settingData = array_merge([
                'key' => $key,
                'value' => $value,
                'type' => $type,
                'group' => $options['group'] ?? 'general',
                'description' => $options['description'] ?? '',
                'is_public' => $options['is_public'] ?? false,
                'is_active' => $options['is_active'] ?? true,
            ], $options);

            $setting = $this->settingRepository->updateByKey($key, $settingData);

            DB::commit();

            return $setting;
        } catch (\Exception $e) {
            DB::rollBack();

            throw new \Exception("Unable to save setting: {$e->getMessage()}");
        }
    }

    /**
     * Get settings by group
     */
    public function getByGroup(string $group): Collection
    {
        try {
            return $this->settingRepository->findByGroup($group);
        } catch (\Exception $e) {
            throw new \Exception("Unable to retrieve settings for group: {$group}");
        }
    }

    /**
     * Get public settings
     */
    public function getPublicSettings(): Collection
    {
        try {
            return $this->settingRepository->findPublicSettings();
        } catch (\Exception $e) {
            throw new \Exception("Unable to retrieve public settings");
        }
    }

    /**
     * Update multiple settings
     */
    public function updateMultiple(array $settings): Collection
    {
        DB::beginTransaction();

        try {
            $updatedSettings = collect();

            foreach ($settings as $key => $data) {
                $value = $data['value'] ?? '';
                $type = $data['type'] ?? 'string';
                $options = $data['options'] ?? [];

                $setting = $this->setValue($key, $value, $type, $options);
                $updatedSettings->push($setting);
            }

            DB::commit();

            return $updatedSettings;
        } catch (\Exception $e) {
            DB::rollBack();

            throw new \Exception("Unable to update settings: {$e->getMessage()}");
        }
    }

    /**
     * Delete setting by key
     */
    public function deleteByKey(string $key): bool
    {
        DB::beginTransaction();

        try {
            $deleted = $this->settingRepository->deleteByKey($key);

            DB::commit();

            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();

            throw new \Exception("Unable to delete setting: {$e->getMessage()}");
        }
    }

    /**
     * Get all setting groups
     */
    public function getAllGroups(): Collection
    {
        try {
            return $this->settingRepository->findAllGroups();
        } catch (\Exception $e) {
            throw new \Exception("Unable to retrieve setting groups");
        }
    }

    /**
     * Activate setting
     */
    public function activate(string $key): SettingEntity
    {
        return $this->toggleStatus($key, true);
    }

    /**
     * Deactivate setting
     */
    public function deactivate(string $key): SettingEntity
    {
        return $this->toggleStatus($key, false);
    }

    /**
     * Toggle setting status
     */
    private function toggleStatus(string $key, bool $status): SettingEntity
    {
        DB::beginTransaction();

        try {
            $setting = $this->settingRepository->findByKey($key);
            if (!$setting) {
                throw new \Exception("Setting not found: {$key}");
            }

            $updatedSetting = $this->settingRepository->update($setting->id, ['is_active' => $status]);

            DB::commit();

            return $updatedSetting;
        } catch (\Exception $e) {
            DB::rollBack();

            throw new \Exception("Unable to update setting status: {$e->getMessage()}");
        }
    }
}
