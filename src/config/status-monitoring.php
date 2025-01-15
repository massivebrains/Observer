<?php declare(strict_types=1);

use App\Enums\PolicyType;
use App\Jobs\Policies\Pages\ComprehensiveHealth\ComprehensiveHealthManagerJob;
use App\Jobs\Policies\Pages\GeneralHealthJob;

return [
    'auth-token' => env('API_AUTH_TOKEN', ''),
    'policies' => [
        PolicyType::PAGES_HEALTH_GENERAL->value => GeneralHealthJob::class,
        PolicyType::PAGES_HEALTH_COMPREHENSIVE->value => ComprehensiveHealthManagerJob::class,
    ],
];
