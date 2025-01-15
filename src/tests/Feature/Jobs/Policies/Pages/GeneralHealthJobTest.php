<?php declare(strict_types=1);

namespace Tests\Feature\Jobs\Policies\Pages;

use App\Enums\Severity;
use App\Enums\SubjectType;
use App\Events\PolicyRunCompletedEvent;
use App\Jobs\Policies\Pages\GeneralHealthJob;
use App\PolicyRun;
use App\PolicyRunDetail;
use Event;
use Http;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Log;
use Tests\TestCase;

class GeneralHealthJobTest extends TestCase
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
                return Str::contains($message, 'job:general-health')
                && Str::contains($value['message'], 'Monolith Api returned http status code 500');
            });

        GeneralHealthJob::dispatchSync($policyRun);

        Event::assertNotDispatched(PolicyRunCompletedEvent::class);
    }

    public function testHandleWithSubjectsFailingMajorOutage()
    {
        Event::fake();

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

        GeneralHealthJob::dispatchSync($policyRun);

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

        $this->assertNotNull($policyRun->fresh()->completed_at);
        Event::assertDispatched(PolicyRunCompletedEvent::class, function (PolicyRunCompletedEvent $event): bool {
            $this->assertEquals(Severity::MAJOR_OUTAGE, $event->severity);

            return true;
        });
    }

    public function testHandleWithSubjectsHealthy()
    {
        Event::fake();

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

        GeneralHealthJob::dispatchSync($policyRun);

        $this->assertDatabaseCount('policy_run_details', 0);
        $this->assertNotNull($policyRun->fresh()->completed_at);

        Event::assertDispatched(PolicyRunCompletedEvent::class, function (PolicyRunCompletedEvent $event): bool {
            $this->assertEquals(Severity::NONE, $event->severity);

            return true;
        });
    }

    public function testHandleWithSubjectsFailingPartialOutage()
    {
        Event::fake();

        $policyRun = PolicyRun::factory()->create();
        $fakeDomain = $policyRun->policy->tenant_domain;

        $fakeSubject1 = $this->getFakeSubject();
        $fakeSubject2 = $this->getFakeSubject();
        $fakeSubject3 = $this->getFakeSubject();
        $fakeSubject4 = $this->getFakeSubject();
        $fakeSubject5 = $this->getFakeSubject();
        $fakeSubject6 = $this->getFakeSubject();

        $fakeSubjectResponse = [
            'data' => ['subjects' => [$fakeSubject1, $fakeSubject2, $fakeSubject3, $fakeSubject4, $fakeSubject5, $fakeSubject6]],
        ];

        Http::fake([
            $this->getFakeMonolithApi($fakeDomain) => Http::response($fakeSubjectResponse, 200),
            $fakeSubject1['id'] => Http::response([], 200),
            $fakeSubject2['id'] => Http::response([], 200),
            $fakeSubject3['id'] => Http::response([], 200),
            $fakeSubject4['id'] => Http::response([], 200),
            $fakeSubject5['id'] => Http::response([], 200),
            $fakeSubject6['id'] => Http::response([], 500),
        ]);

        GeneralHealthJob::dispatchSync($policyRun);

        $this->assertDatabaseCount('policy_run_details', 1);
        $this->assertDatabaseHas('policy_run_details', [
            'policy_run_id' => $policyRun->id,
            'subject_type' => SubjectType::URL,
            'subject_id' => $fakeSubject6['id'],
        ]);

        Event::assertDispatched(PolicyRunCompletedEvent::class, function (PolicyRunCompletedEvent $event): bool {
            $this->assertEquals(Severity::PARTIAL_OUTAGE, $event->severity);

            return true;
        });
    }
}
