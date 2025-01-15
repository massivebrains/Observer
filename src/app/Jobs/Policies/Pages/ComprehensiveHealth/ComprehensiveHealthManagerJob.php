<?php declare(strict_types=1);

namespace App\Jobs\Policies\Pages\ComprehensiveHealth;

use App\Events\ComprehensiveHealth\CompletedEvent;
use App\Jobs\Policies\BasePolicyJob;
use App\Services\Monolith\MonolithHttpService;
use Illuminate\Bus\Batch;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Bus;
use Log;
use Throwable;

class ComprehensiveHealthManagerJob extends BasePolicyJob
{
    public const SUBJECTS_PER_PAGE = 100;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $monolithHttpService = app(MonolithHttpService::class);

        try {
            $meta = $monolithHttpService->getComprehensiveHealthMeta($this->policyRun->policy);
            $total = (int) Arr::get($meta, 'total', 0);
            if ($total <= 0) {
                return;
            }

            $pagesCount = (int) ceil($total / self::SUBJECTS_PER_PAGE);
            $comprehensiveHealthJobs = collect()
                ->range(1, $pagesCount)
                ->map(function (int $pageNumber) {
                    return new ComprehensiveHealthJob($this->policyRun, $pageNumber);
                });

            Bus::batch($comprehensiveHealthJobs)
                ->name(sprintf('Comprehensive Health Batch for Policy Run ID: %d', $this->policyRun->id))
                ->withOption('policyRunId', $this->policyRun->id)
                ->withOption('totalSubjects', $total)
                ->before(fn (Batch $batch) => Log::info('bus:comprehensive:batch:before', ['batchId' => $batch->id, 'jobCount' => $batch->totalJobs]))
                ->progress(fn (Batch $batch) => Log::info('bus:comprehensive:batch:progress', ['batchId' => $batch->id, 'progress' => $batch->progress()]))
                ->then(function (Batch $batch) {
                    Log::info('bus:comprehensive:batch:then', ['batchId' => $batch->id]);
                    CompletedEvent::dispatch($batch->options['policyRunId'], $batch->options['totalSubjects']);
                })
                ->catch(fn (Batch $batch, Throwable $e) => Log::info('bus:comprehensive:batch:catch', ['batchId' => $batch->id, 'error' => $e->getMessage()]))
                ->dispatch();
        } catch (Throwable $th) {
            Log::error('job:comprehensive-health-manager:error', ['message' => $th->getMessage()]);
        }
    }
}
