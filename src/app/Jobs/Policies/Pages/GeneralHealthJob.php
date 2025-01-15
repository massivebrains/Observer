<?php declare(strict_types=1);

namespace App\Jobs\Policies\Pages;

use App\Enums\PolicyRunStatus;
use App\Enums\Severity;
use App\Events\PolicyRunCompletedEvent;
use App\Services\Monolith\MonolithHttpService;
use Log;
use Throwable;

class GeneralHealthJob extends BasePagesPolicyJob
{
    private const MAXIMUM_PARTIAL_OUTAGE_PERCENTAGE = 0.2;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $monolithHttpService = app(MonolithHttpService::class);

        try {
            $subjects = $monolithHttpService->getGeneralHealthSubjects($this->policyRun->policy);
            $policyRunDetails = $this->ping($subjects);

            if ($policyRunDetails->isEmpty()) {
                $this->policyRun->complete();
            } else {
                $this->policyRun->details()->createMany($policyRunDetails);
                $this->policyRun->complete(PolicyRunStatus::COMPLETED_WITH_ERRORS);
            }

            PolicyRunCompletedEvent::dispatch(
                $this->calculateSeverity($subjects),
                $this->policyRun
            );
        } catch (Throwable $th) {
            Log::error('job:general-health', ['message' => $th->getMessage()]);
        }
    }

    private function calculateSeverity(array $subjects): Severity
    {
        $total = count($subjects);
        $detailsCount = $this->policyRun->fresh()->details->count();

        if ($detailsCount === 0) {
            return Severity::NONE;
        }

        if (($detailsCount / $total) >= self::MAXIMUM_PARTIAL_OUTAGE_PERCENTAGE) {
            return Severity::MAJOR_OUTAGE;
        }

        return Severity::PARTIAL_OUTAGE;
    }
}
