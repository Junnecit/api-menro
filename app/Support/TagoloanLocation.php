<?php

namespace App\Support;

class TagoloanLocation
{
    public static function barangayCodes(): array
    {
        return array_keys(config('tagoloan.barangays', []));
    }

    public static function barangayName(string $code): ?string
    {
        return config("tagoloan.barangays.{$code}");
    }

    public static function formatLocation(string $barangayCode): string
    {
        $name = self::barangayName($barangayCode);

        if (! $name) {
            return $barangayCode;
        }

        return "{$name}, ".config('tagoloan.location_suffix');
    }

    public static function applyBarangay(array $data): array
    {
        if (! empty($data['barangay_code'])) {
            $data['location'] = self::formatLocation($data['barangay_code']);
        }

        return $data;
    }
}
