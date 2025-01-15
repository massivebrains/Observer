<?php declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SubjectType;
use App\Policy;
use App\PolicyRun;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PolicyRun>
 */
class PolicyRunFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => 1,
            'policy_id' => Policy::factory(),
        ];
    }

    public function completedWithErrors(?Policy $policy = null): PolicyRun
    {
        $policy ??= Policy::factory()->findOrCreate();

        $policyRun = $this->state(function () use ($policy) {
            return [
                'policy_id' => $policy->id,
                'completed_at' => now(),
            ];
        })->create();

        $policyRun->details()->create([
            'subject_type' => SubjectType::URL,
            'subject_id' => $this->faker->url(),
            'meta' => [],
        ]);

        return $policyRun;
    }
}
