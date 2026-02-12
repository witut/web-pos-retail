<?php

namespace App\Services;

use App\Models\Setting;

/**
 * SettingService
 * 
 * Centralized service untuk manage settings dengan type-safe helpers
 */
class SettingService
{
    /**
     * Get setting value by key
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return Setting::get($key, $default);
    }

    /**
     * Get setting as boolean
     * 
     * @param string $key
     * @param bool $default
     * @return bool
     */
    public function getBool(string $key, bool $default = false): bool
    {
        $value = $this->get($key, $default);

        // Handle string boolean values
        if (is_string($value)) {
            return in_array(strtolower($value), ['1', 'true', 'yes', 'on']);
        }

        return (bool) $value;
    }

    /**
     * Get setting as integer
     * 
     * @param string $key
     * @param int $default
     * @return int
     */
    public function getInt(string $key, int $default = 0): int
    {
        return (int) $this->get($key, $default);
    }

    /**
     * Get setting as float
     * 
     * @param string $key
     * @param float $default
     * @return float
     */
    public function getFloat(string $key, float $default = 0.0): float
    {
        return (float) $this->get($key, $default);
    }

    /**
     * Get setting as array
     * 
     * @param string $key
     * @param array $default
     * @return array
     */
    public function getArray(string $key, array $default = []): array
    {
        $value = $this->get($key, $default);

        if (is_array($value)) {
            return $value;
        }

        // Try to decode JSON string
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : $default;
        }

        return $default;
    }

    /**
     * Set setting value
     * 
     * @param string $key
     * @param mixed $value
     * @param string|null $group
     * @param string|null $description
     * @return Setting
     */
    public function set(string $key, $value, ?string $group = null, ?string $description = null): Setting
    {
        // Encode array/object to JSON
        if (is_array($value) || is_object($value)) {
            $value = json_encode($value);
        }

        // Convert boolean to string
        if (is_bool($value)) {
            $value = $value ? '1' : '0';
        }

        return Setting::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'group' => $group,
                'description' => $description,
            ]
        );
    }

    /**
     * Get all settings by group
     * 
     * @param string $group
     * @return array
     */
    public function getByGroup(string $group): array
    {
        $settings = Setting::where('group', $group)->get();
        $result = [];

        foreach ($settings as $setting) {
            $decoded = json_decode($setting->value, true);
            $result[$setting->key] = $decoded ?? $setting->value;
        }

        return $result;
    }

    /**
     * Check if setting exists
     * 
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return Setting::has($key);
    }

    /**
     * Remove setting
     * 
     * @param string $key
     * @return bool
     */
    public function remove(string $key): bool
    {
        return Setting::remove($key);
    }

    /**
     * Get all settings grouped by group
     * 
     * @return array
     */
    public function getAllGrouped(): array
    {
        $settings = Setting::all();
        $grouped = [];

        foreach ($settings as $setting) {
            $group = $setting->group ?? 'general';
            $decoded = json_decode($setting->value, true);

            if (!isset($grouped[$group])) {
                $grouped[$group] = [];
            }

            $grouped[$group][$setting->key] = $decoded ?? $setting->value;
        }

        return $grouped;
    }
}
