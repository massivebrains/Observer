<?php declare(strict_types=1);

namespace App\Enums;

enum PolicyStatus: string
{
    case ACTIVE = 'ACTIVE';
    case INACTIVE = 'INACTIVE';
}
