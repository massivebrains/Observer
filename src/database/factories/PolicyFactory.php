<?php declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PolicyStatus;
use App\Enums\PolicyType;
use App\Enums\Product;
use App\Policy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Policy>
 */
class PolicyFactory extends Factory
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
            'tenant_domain' => $this->faker->domainName(),
            'product' => Product::LOCAL_LANDING_PAGES,
            'status' => PolicyStatus::ACTIVE,
            'type' => PolicyType::PAGES_HEALTH_GENERAL,
        ];
    }

    public function findOrCreate(int $accountId = 1, PolicyType $type = PolicyType::PAGES_HEALTH_GENERAL)
    {
        return Policy::firstOrCreate(['account_id' => $accountId, 'type' => $type], $this->raw());
    }

    public function inactive(): Factory
    {
        return $this->state(function () {
            return [
                'status' => PolicyStatus::INACTIVE,
            ];
        });
    }

    public function pagesHealthComprehensive(): Factory
    {
        return $this->state(function () {
            return [
                'type' => PolicyType::PAGES_HEALTH_COMPREHENSIVE,
            ];
        });
    }
}
