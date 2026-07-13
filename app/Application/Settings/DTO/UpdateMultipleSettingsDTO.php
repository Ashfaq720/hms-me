<?php

namespace App\Application\Settings\DTO;

class UpdateMultipleSettingsDTO
{
    public function __construct(
        public readonly array $settings,
        public readonly ?string $group = null
    ) {
        $this->validate();
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            settings: $data['settings'] ?? [],
            group: $data['group'] ?? null
        );
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function getGroup(): ?string
    {
        return $this->group;
    }

    public function toArray(): array
    {
        return [
            'settings' => $this->settings,
            'group' => $this->group,
        ];
    }

    private function validate(): void
    {
        if (empty($this->settings)) {
            throw new \InvalidArgumentException('Settings array cannot be empty');
        }

        foreach ($this->settings as $key => $setting) {
            if (!is_string($key) || empty($key)) {
                throw new \InvalidArgumentException('Setting key must be a non-empty string');
            }

            if (!isset($setting['value'])) {
                throw new \InvalidArgumentException("Setting value is required for key: {$key}");
            }
        }
    }
}
