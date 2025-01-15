<?php declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Policies;

use App\Enums\PolicyStatus;
use App\Enums\PolicyType;
use App\Policy;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Http\BaseApi;

class DeleteControllerTest extends BaseApi
{
    public function testSuccessIgnoreInactivePolicies()
    {
        $policy1 = Policy::factory()->create();
        $policy2 = Policy::factory()->pagesHealthComprehensive()->inactive()->create();

        $response = $this->api()->json('DELETE', '/api/policies', [
            'account_id' => 1,
            'policies' => [$policy1->type, $policy2->type],
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseCount('policies', 2);

        $this->assertDatabaseHas('policies', [
            'account_id' => 1,
            'type' => $policy1->type,
            'status' => PolicyStatus::INACTIVE,
        ]);

        $this->assertDatabaseHas('policies', [
            'account_id' => 1,
            'type' => $policy2->type,
            'status' => PolicyStatus::INACTIVE,
        ]);
    }

    public function testSuccessIgnoreOtherPolicies()
    {
        Policy::factory()->create();
        Policy::factory()->pagesHealthComprehensive()->inactive()->create();

        $response = $this->api()->json('DELETE', '/api/policies', [
            'account_id' => 1,
            'policies' => [PolicyType::PAGES_HEALTH_COMPREHENSIVE],
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseCount('policies', 2);

        $this->assertDatabaseHas('policies', [
            'account_id' => 1,
            'type' => PolicyType::PAGES_HEALTH_GENERAL,
            'status' => PolicyStatus::ACTIVE,
        ]);

        $this->assertDatabaseHas('policies', [
            'account_id' => 1,
            'type' => PolicyType::PAGES_HEALTH_COMPREHENSIVE,
            'status' => PolicyStatus::INACTIVE,
        ]);
    }
}
