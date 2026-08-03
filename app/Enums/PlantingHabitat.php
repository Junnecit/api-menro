<?php

namespace App\Enums;

enum PlantingHabitat: string
{
    case Terrestrial = 'terrestrial';
    case Mangrove = 'mangrove';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
