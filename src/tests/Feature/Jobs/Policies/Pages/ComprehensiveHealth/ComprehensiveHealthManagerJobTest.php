<?php declare(strict_types=1);

namespace Tests\Feature\Jobs\Policies\Pages;

use App\Jobs\Policies\Pages\ComprehensiveHealth\ComprehensiveHealthManagerJob;
use App\PolicyRun;
use Http;
use Illuminate\Bus\PendingBatch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;
use Log;
use Tests\TestCase;

class ComprehensiveHealthManagerJobTest extends TestCase
{
    private function getFakeMonolithApi(string $domain): string
    {
        return sprintf('https://%s*', $domain);
    }

    public function testHandleWhenGetComprehensiveHealthMetaThrows()
    {
        $policyRun = PolicyRun::factory()->create();
        $fakeDomain = $policyRun->policy->tenant_domain;

        Http::fake([$this->getFakeMonolithApi($fakeDomain) => Http::response([], 500)]);

        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $value) {
                return Str::contains($message, 'job:comprehensive-health-manager:error')
                && Str::contains($value['message'], 'Monolith Api returned http status code 500');
            });

        (new ComprehensiveHealthManagerJob($policyRun))->handle();
    }

    public function testHandleWhenGetComprehensiveHealthMetaReturnsZero()
    {
        Bus::fake();

        $policyRun = PolicyRun::factory()->create();
        $fakeDomain = $policyRun->policy->tenant_domain;

        Http::fake([$this->getFakeMonolithApi($fakeDomain) => Http::response(['data' => ['meta' => ['total' => 0]]], 200)]);

        (new ComprehensiveHealthManagerJob($policyRun))->handle();

        Bus::assertNothingBatched();
    }

    public function testHandleSuccess()
    {
        Bus::fake();

        $policyRun = PolicyRun::factory()->create();
        $fakeDomain = $policyRun->policy->tenant_domain;

        Http::fake([$this->getFakeMonolithApi($fakeDomain) => Http::response(['data' => ['meta' => ['total' => 499]]], 200)]);

        (new ComprehensiveHealthManagerJob($policyRun))->handle();

        Bus::assertBatched(function (PendingBatch $batch) {
            return $batch->jobs[0]->pageNumber === 1
                && $batch->jobs[1]->pageNumber === 2
                && $batch->jobs[2]->pageNumber === 3
                && $batch->jobs[3]->pageNumber === 4
                && $batch->jobs[4]->pageNumber === 5
                && $batch->jobs->count() === 5;
        });
    }
}
