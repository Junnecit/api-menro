<?php

namespace App\Support;

class PsgcLocation
{
    public static function formatLocation(array $data): string
    {
        $parts = array_filter([
            $data['barangay_name'] ?? null,
            $data['municipality_name'] ?? null,
            $data['province_name'] ?? null,
            $data['region_name'] ?? null,
            'Philippines',
        ], fn ($value) => filled($value));

        return implode(', ', $parts);
    }

    public static function applyAddress(array $data): array
    {
        if (! empty($data['barangay_code'])) {
            $data['location'] = self::formatLocation($data);
        } elseif (array_key_exists('barangay_code', $data) && empty($data['barangay_code'])) {
            $data['location'] = null;
            $data['region_code'] = null;
            $data['province_code'] = null;
            $data['municipality_code'] = null;
        }

        unset(
            $data['region_name'],
            $data['province_name'],
            $data['municipality_name'],
            $data['barangay_name'],
        );

        return $data;
    }
}
