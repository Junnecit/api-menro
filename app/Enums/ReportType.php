<?php

namespace App\Enums;

enum ReportType: string
{
    case Damage = 'damage';
    case DiseasePest = 'disease_pest';
    case IllegalCutting = 'illegal_cutting';
    case ReplacementNeeded = 'replacement_needed';
    case Hazard = 'hazard';
    case ScheduledInspection = 'scheduled_inspection';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Damage => 'Physical / Storm Damage',
            self::DiseasePest => 'Disease / Pest Infestation',
            self::IllegalCutting => 'Illegal Cutting / Vandalism',
            self::ReplacementNeeded => 'Need Replacement / Mortality',
            self::Hazard => 'Safety / Tree Hazard',
            self::ScheduledInspection => 'Routine / Scheduled Inspection',
            self::Other => 'Other Field Observation',
        };
    }
}
