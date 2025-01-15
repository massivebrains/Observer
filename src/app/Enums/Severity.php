<?php declare(strict_types=1);

namespace App\Enums;

enum Severity: string
{
    case NONE = 'NONE';
    case PARTIAL_OUTAGE = 'PARTIAL_OUTAGE';
    case MAJOR_OUTAGE = 'MAJOR_OUTAGE';
}
