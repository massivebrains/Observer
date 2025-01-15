<?php declare(strict_types=1);

namespace App\Listeners\ComprehensiveHealth;

use App\Enums\PolicyRunStatus;
use App\Enums\Severity;
use App\Events\ComprehensiveHealth\CompletedEvent;
use App\Events\PolicyRunCompletedEvent;

class ManagerCompletedListener
{
    private const MAXIMUM_PARTIAL_OUTAGE_PERCENTAGE = 0.2;

    /**
     * Handle the event.
     */
    public function handle(CompletedEvent $event): void
    {
        $status = $event->policyRun->details()->count() ? PolicyRunStatus::COMPLETED_WITH_ERRORS : PolicyRunStatus::COMPLETED;
        $event->policyRun->complete($status);

        PolicyRunCompletedEvent::dispatch($this->calculateSeverity($event), $event->policyRun);
    }

    private function calculateSeverity(CompletedEvent $event): Severity
    {
        $detailsCount = $event->policyRun->fresh()->details->count();
        if ($detailsCount === 0) {
            return Severity::NONE;
        }

        if (($detailsCount / $event->totalSubjects) >= self::MAXIMUM_PARTIAL_OUTAGE_PERCENTAGE) {
            return Severity::MAJOR_OUTAGE;
        }

        return Severity::PARTIAL_OUTAGE;
    }
}
