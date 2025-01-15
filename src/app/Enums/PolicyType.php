<?php declare(strict_types=1);

namespace App\Enums;

enum PolicyType: string
{
    case PAGES_HEALTH_GENERAL = 'PAGES_HEALTH_GENERAL';
    case PAGES_HEALTH_COMPREHENSIVE = 'PAGES_HEALTH_COMPREHENSIVE';
}
