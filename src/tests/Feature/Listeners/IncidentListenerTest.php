<?php declare(strict_types=1);

namespace Tests\Feature\Listeners;

use App\Enums\IncidentStatus;
use App\Enums\Severity;
use App\Events\PolicyRunCompletedEvent;
use App\Incident;
use App\Listeners\IncidentListener;
use App\Notifications\IncidentClosedNotification;
use App\Notifications\IncidentOpenedNotification;
use App\Policy;
use App\PolicyRun;
use Illuminate\Foundation\Testing\WithFaker;
use Notification;
use Tests\TestCase;

class IncidentListenerTest extends TestCase
{
    use WithFaker;

    public function testHandleDoesNotCreateDuplicateOpenIncidents()
    {
        Notification::fake();

        $existingOpenIncident = Incident::factory()->create();
        $newPolicyRun = PolicyRun::factory()->completedWithErrors($existingOpenIncident->policy);

        (new IncidentListener())->handle(new PolicyRunCompletedEvent(Severity::PARTIAL_OUTAGE, $newPolicyRun));

        $this->assertDatabaseCount('incidents', 1);
        $this->assertModelExists($existingOpenIncident);

        Notification::assertNothingSent();
    }

    public function testHandleCreatesOpenIncident()
    {
        Notification::fake();

        $newPolicyRun = PolicyRun::factory()->completedWithErrors();
        (new IncidentListener())->handle(new PolicyRunCompletedEvent(Severity::PARTIAL_OUTAGE, $newPolicyRun));

        $this->assertDatabaseCount('incidents', 1);
        $this->assertDatabaseHas('incidents', [
            'open_policy_run_id' => $newPolicyRun->id,
            'close_policy_run_id' => null,
            'status' => IncidentStatus::OPEN,
            'closed_at' => null,
        ]);

        Notification::assertSentTo($newPolicyRun->policy, IncidentOpenedNotification::class);
        Notification::assertNotSentTo($newPolicyRun->policy, IncidentClosedNotification::class);
    }

    public function testHandleClosesAnExistingIncident()
    {
        Notification::fake();

        $existingOpenIncident = Incident::factory()->create();
        $newPolicyRun = PolicyRun::factory()->create([
            'policy_id' => $existingOpenIncident->policy,
            'completed_at' => now(),
        ]);

        (new IncidentListener())->handle(new PolicyRunCompletedEvent(Severity::NONE, $newPolicyRun));

        $existingOpenIncident->refresh();

        $this->assertDatabaseCount('incidents', 1);
        $this->assertDatabaseHas('incidents', [
            'open_policy_run_id' => $existingOpenIncident->openPolicyRun->id,
            'close_policy_run_id' => $existingOpenIncident->closePolicyRun->id,
            'status' => IncidentStatus::CLOSED,
        ]);

        Notification::assertNotSentTo($newPolicyRun->policy, IncidentOpenedNotification::class);
        Notification::assertSentTo($newPolicyRun->policy, IncidentClosedNotification::class);
    }

    public function testHandleDoesNothingWhenThereIsNoIncidentToClose()
    {
        Notification::fake();

        $closePolicyRun = PolicyRun::factory()->create([
            'policy_id' => Policy::factory()->create(['account_id' => 10]),
            'completed_at' => now(),
        ]);

        $existingClosedIncident = Incident::factory()->create([
            'status' => IncidentStatus::CLOSED,
            'close_policy_run_id' => $closePolicyRun->id,
        ]);

        $newPolicyRun = PolicyRun::factory()->create([
            'policy_id' => Policy::factory()->create(['account_id' => 12]),
            'completed_at' => now(),
        ]);

        (new IncidentListener())->handle(new PolicyRunCompletedEvent(Severity::NONE, $newPolicyRun));

        $this->assertDatabaseCount('incidents', 1);
        $this->assertModelExists($existingClosedIncident);

        Notification::assertNothingSent();
    }

    public function testPolicyFormattedType()
    {
        $this->assertEquals('Pages Health General', Policy::factory()->create()->formattedType);
    }

    public function testIncidentFormattedSeverity()
    {
        $this->assertEquals('Partial Outage', Incident::factory()->create()->formattedSeverity);
    }
}
