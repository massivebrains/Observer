<?php declare(strict_types=1);

namespace Tests\Feature\Listeners;

use App\Enums\PolicyRunStatus;
use App\Enums\Severity;
use App\Events\ComprehensiveHealth\CompletedEvent;
use App\Events\PolicyRunCompletedEvent;
use App\Listeners\ComprehensiveHealth\ManagerCompletedListener;
use App\PolicyRun;
use App\PolicyRunDetail;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ManagerCompletedListenerTest extends TestCase
{
    public function testHandleUpdatePolicyRunToCompleted()
    {
        Event::fake();

        $policyRun = PolicyRun::factory()->create();
        $completedEvent = new CompletedEvent($policyRun->id, 30);

        (new ManagerCompletedListener())->handle($completedEvent);

        Event::assertDispatched(PolicyRunCompletedEvent::class, function (PolicyRunCompletedEvent $event): bool {
            return $event->severity === Severity::NONE;
        });

        $this->assertDatabaseHas('policy_runs', [
            'id' => $policyRun->id,
            'status' => PolicyRunStatus::COMPLETED,
        ]);
    }

    public function testHandleUpdatePolicyRunToCompletedWithErrorsHavingMajorOutage()
    {
        Event::fake();

        $policyRun = PolicyRun::factory()->create();
        PolicyRunDetail::factory()->times(1)->create(['policy_run_id' => $policyRun->id]);
        $completedEvent = new CompletedEvent($policyRun->id, 2);

        (new ManagerCompletedListener())->handle($completedEvent);

        Event::assertDispatched(PolicyRunCompletedEvent::class, function (PolicyRunCompletedEvent $event): bool {
            return $event->severity === Severity::MAJOR_OUTAGE;
        });

        $this->assertDatabaseHas('policy_runs', [
            'id' => $policyRun->id,
            'status' => PolicyRunStatus::COMPLETED_WITH_ERRORS,
        ]);
    }

    public function testHandleUpdatePolicyRunToCompletedWithErrorsHavingPartialOutage()
    {
        Event::fake();

        $policyRun = PolicyRun::factory()->create();
        PolicyRunDetail::factory()->times(2)->create(['policy_run_id' => $policyRun->id]);
        $completedEvent = new CompletedEvent($policyRun->id, 12);

        (new ManagerCompletedListener())->handle($completedEvent);

        Event::assertDispatched(PolicyRunCompletedEvent::class, function (PolicyRunCompletedEvent $event): bool {
            return $event->severity === Severity::PARTIAL_OUTAGE;
        });

        $this->assertDatabaseHas('policy_runs', [
            'id' => $policyRun->id,
            'status' => PolicyRunStatus::COMPLETED_WITH_ERRORS,
        ]);
    }
}
