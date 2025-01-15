<?php declare(strict_types=1);

namespace Database\Factories;

use App\Enums\IncidentStatus;
use App\Enums\Severity;
use App\Policy;
use App\PolicyRun;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Incident>
 */
class IncidentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $policy = Policy::factory()->findOrCreate();

        return [
            'account_id' => 1,
            'policy_id' => $policy->id,
            'open_policy_run_id' => PolicyRun::factory()->create(['policy_id' => $policy->id])->id,
            'close_policy_run_id' => null,
            'status' => IncidentStatus::OPEN,
            'severity' => Severity::PARTIAL_OUTAGE,
            'closed_at' => null,
        ];
    }
}
