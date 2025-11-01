<?php

namespace App\Enums;

enum TaskStatus: string
{
    case OPEN = 'Open';
    case IN_PROGRESS = 'In Progress';
    case COMPLETED = 'Completed';
    case REJECTED = 'Rejected';

    /**
     * Get all enum values as array of strings.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}