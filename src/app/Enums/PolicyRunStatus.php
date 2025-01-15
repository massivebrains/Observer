<?php declare(strict_types=1);

namespace App\Enums;

enum PolicyRunStatus: string
{
    case PENDING = 'PENDING';
    case COMPLETED = 'COMPLETED';
    case COMPLETED_WITH_ERRORS = 'COMPLETED_WITH_ERRORS';
}
