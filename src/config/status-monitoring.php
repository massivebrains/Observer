<?php declare(strict_types=1);

use App\Enums\PolicyType;
use App\Jobs\Policies\Pages\ComprehensiveHealth\ComprehensiveHealthManagerJob;
use App\Jobs\Policies\Pages\GeneralHealthJob;

return [
    'auth-token' => env('API_AUTH_TOKEN', '4a63bb7c-1e14-4cf0-ba31-ddcddf79b277'),
    'policies' => [
        PolicyType::PAGES_HEALTH_GENERAL->value => GeneralHealthJob::class,
        PolicyType::PAGES_HEALTH_COMPREHENSIVE->value => ComprehensiveHealthManagerJob::class,
    ],
];
