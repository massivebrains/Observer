<?php declare(strict_types=1);

use App\Console\Commands\RunPolicyCommand;
use App\Enums\PolicyType;

$showOutput = static function (Stringable $output): void {
    echo $output;
};

Schedule::command(RunPolicyCommand::class, [PolicyType::PAGES_HEALTH_GENERAL->value])->everyFiveMinutes()->thenWithOutput($showOutput);
Schedule::command(RunPolicyCommand::class, [PolicyType::PAGES_HEALTH_COMPREHENSIVE->value])->daily()->thenWithOutput($showOutput);
