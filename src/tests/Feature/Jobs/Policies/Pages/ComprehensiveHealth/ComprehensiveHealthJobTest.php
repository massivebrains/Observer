<?php declare(strict_types=1);

namespace Tests\Feature\Jobs\Policies\Pages;

use App\Enums\SubjectType;
use App\Jobs\Policies\Pages\ComprehensiveHealth\ComprehensiveHealthJob;
use App\PolicyRun;
use App\PolicyRunDetail;
use Http;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Log;
use Tests\TestCase;

class ComprehensiveHealthJobTest extends TestCase
{
    use WithFaker;

    private function getFakeSubject(): array
    {
        return [
            'type' => SubjectType::URL,
            'id' => $this->faker->url(),
            'meta' => [
                'name' => $this->faker->name(),
                'entity_id' => $this->faker->randomNumber(),
                'remote_id' => $this->faker->uuid(),
            ],
        ];
    }

    private function getFakeMonolithApi(string $domain): string
    {
        return sprintf('https://%s/*', $domain);
    }

    public function testHandleThrowsMonolithException()
    {
        Event::fake();

        $policyRun = PolicyRun::factory()->create();
        $fakeDomain = $policyRun->policy->tenant_domain;

        Http::fake([$this->getFakeMonolithApi($fakeDomain) => Http::response([], 500)]);

        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $value) {
                return Str::contains($message, 'job:comprehensive-health')
                && Str::contains($value['message'], 'Monolith Api returned http status code 500');
            });

        ComprehensiveHealthJob::dispatchSync($policyRun, 1);
    }

    public function testHandleWithSubjectsFailingCreatesPolicyRunDetails()
    {
        $policyRun = PolicyRun::factory()->create();
        $fakeDomain = $policyRun->policy->tenant_domain;

        $fakeSubject1 = $this->getFakeSubject();
        $fakeSubject2 = $this->getFakeSubject();

        $fakeSubjectResponse = [
            'data' => ['subjects' => [$fakeSubject1, $fakeSubject2]],
        ];

        Http::fake([
            $this->getFakeMonolithApi($fakeDomain) => Http::response($fakeSubjectResponse, 200),
            $fakeSubject1['id'] => Http::response([], 200),
            $fakeSubject2['id'] => Http::response([], 500),
        ]);

        ComprehensiveHealthJob::dispatchSync($policyRun, 1);

        $this->assertDatabaseCount('policy_run_details', 1);
        $this->assertDatabaseHas('policy_run_details', [
            'policy_run_id' => $policyRun->id,
            'subject_type' => SubjectType::URL,
            'subject_id' => $fakeSubject2['id'],
        ]);

        $this->assertEquals(
            $fakeSubject2['meta'],
            PolicyRunDetail::wherePolicyRunId($policyRun->id)->first()->meta
        );
    }

    public function testHandleWithSubjectsHealthy()
    {
        $policyRun = PolicyRun::factory()->create();
        $fakeDomain = $policyRun->policy->tenant_domain;

        $fakeSubject1 = $this->getFakeSubject();
        $fakeSubject2 = $this->getFakeSubject();

        $fakeSubjectResponse = [
            'data' => ['subjects' => [$fakeSubject1, $fakeSubject2]],
        ];

        Http::fake([
            $this->getFakeMonolithApi($fakeDomain) => Http::response($fakeSubjectResponse, 200),
            $fakeSubject1['id'] => Http::sequence()->push([], 500)->push([], 500)->push([], 200),
            $fakeSubject2['id'] => Http::sequence()->push([], 500)->push([], 200),
        ]);

        ComprehensiveHealthJob::dispatchSync($policyRun, 1);

        $this->assertDatabaseCount('policy_run_details', 0);
    }
}
