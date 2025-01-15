<?php declare(strict_types=1);

namespace Tests\Feature\Policies;

use App\Console\Commands\RunPolicyCommand;
use App\Enums\PolicyType;
use App\Jobs\Policies\Pages\GeneralHealthJob;
use App\Policy;
use Queue;
use Tests\TestCase;

class RunPolicyCommandTest extends TestCase
{
    public function testHandleWithNoEnabledPolicies()
    {
        Queue::fake();

        Policy::factory()->inactive()->create();
        $this->artisan(RunPolicyCommand::class, ['type' => PolicyType::PAGES_HEALTH_GENERAL->value])->assertSuccessful();

        Queue::assertNothingPushed();

        $this->assertDatabaseCount('policy_runs', 0);
    }

    public function testHandleWithEnabledPolicies()
    {
        Queue::fake();

        Policy::factory()->create();
        Policy::factory()->create(['account_id' => 2]);
        Policy::factory()->create(['account_id' => 3]);

        $this->artisan(RunPolicyCommand::class, ['type' => PolicyType::PAGES_HEALTH_GENERAL->value])->assertSuccessful();

        Queue::assertPushed(GeneralHealthJob::class, 3);

        $this->assertDatabaseHas('policy_runs', ['account_id' => 1]);
        $this->assertDatabaseHas('policy_runs', ['account_id' => 2]);
        $this->assertDatabaseHas('policy_runs', ['account_id' => 3]);
    }
}
