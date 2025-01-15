<?php declare(strict_types=1);

namespace App\Jobs\Policies\Pages\ComprehensiveHealth;

use App\Jobs\Policies\Pages\BasePagesPolicyJob;
use App\PolicyRun;
use App\Services\Monolith\MonolithHttpService;
use Illuminate\Bus\Batchable;
use Log;
use Throwable;

class ComprehensiveHealthJob extends BasePagesPolicyJob
{
    use Batchable;

    public function __construct(protected PolicyRun $policyRun, public int $pageNumber)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $monolithHttpService = app(MonolithHttpService::class);

        try {
            $subjects = $monolithHttpService->getComprehensiveHealthSubjects(
                $this->policyRun->policy,
                $this->pageNumber,
                ComprehensiveHealthManagerJob::SUBJECTS_PER_PAGE
            );

            $policyRunDetails = $this->ping($subjects);
            if ($policyRunDetails->isEmpty()) {
                return;
            }

            $this->policyRun->details()->createMany($policyRunDetails);
        } catch (Throwable $th) {
            Log::error('job:comprehensive-health', ['message' => $th->getMessage(), 'page_number' => $this->pageNumber]);
        }
    }
}
