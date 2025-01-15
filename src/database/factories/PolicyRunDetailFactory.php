<?php declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SubjectType;
use App\PolicyRun;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PolicyRun>
 */
class PolicyRunDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'policy_run_id' => PolicyRun::factory(),
            'subject_type' => SubjectType::URL,
            'subject_id' => $this->faker->url(),
            'meta' => [
                'remote_id' => $this->faker->uuid(),
            ],
        ];
    }
}
