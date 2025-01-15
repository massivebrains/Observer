<?php declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Policies;

use App\Enums\PolicyStatus;
use App\Enums\PolicyType;
use App\Enums\Product;
use App\Policy;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Http\BaseApi;

class StoreControllerTest extends BaseApi
{
    use WithFaker;

    public function testAuthorization()
    {
        $response = $this->json('POST', '/api/policies', []);
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function testTenantDomainValidation()
    {
        $response = $this->api()->json('POST', '/api/policies', [
            'account_id' => 1,
            'tenant_domain' => $this->faker->uuid(),
            'policies' => [PolicyType::PAGES_HEALTH_GENERAL],
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testSuccessUpdateInactivePolicies()
    {
        $policy1 = Policy::factory()->inactive()->create();
        $policy2 = Policy::factory()->pagesHealthComprehensive()->inactive()->create();

        $response = $this->api()->json('POST', '/api/policies', [
            'account_id' => 1,
            'tenant_domain' => $policy1->tenant_domain,
            'product' => Product::LOCAL_LANDING_PAGES->value,
            'policies' => [PolicyType::PAGES_HEALTH_GENERAL, PolicyType::PAGES_HEALTH_COMPREHENSIVE],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseCount('policies', 2);

        $this->assertDatabaseHas('policies', [
            'account_id' => 1,
            'tenant_domain' => $policy1->tenant_domain,
            'status' => PolicyStatus::ACTIVE,
            'type' => PolicyType::PAGES_HEALTH_GENERAL,
        ]);

        $this->assertDatabaseHas('policies', [
            'account_id' => 1,
            'tenant_domain' => $policy2->tenant_domain,
            'status' => PolicyStatus::ACTIVE,
            'type' => PolicyType::PAGES_HEALTH_COMPREHENSIVE,
        ]);
    }

    public function testSuccessUpdateIgnoreOtherPoliciesFromTheSameAccount()
    {
        $policy1 = Policy::factory()->inactive()->create();
        $policy2 = Policy::factory()->pagesHealthComprehensive()->inactive()->create();

        $response = $this->api()->json('POST', '/api/policies', [
            'account_id' => 1,
            'tenant_domain' => $policy1->tenant_domain,
            'product' => Product::LOCAL_LANDING_PAGES->value,
            'policies' => [PolicyType::PAGES_HEALTH_COMPREHENSIVE],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseCount('policies', 2);

        $this->assertDatabaseHas('policies', [
            'account_id' => 1,
            'tenant_domain' => $policy1->tenant_domain,
            'status' => PolicyStatus::INACTIVE,
            'type' => PolicyType::PAGES_HEALTH_GENERAL,
        ]);

        $this->assertDatabaseHas('policies', [
            'account_id' => 1,
            'tenant_domain' => $policy2->tenant_domain,
            'status' => PolicyStatus::ACTIVE,
            'type' => PolicyType::PAGES_HEALTH_COMPREHENSIVE,
        ]);
    }

    public function testSuccessUpdateCreateNewPolicies()
    {
        $policy = Policy::factory()->create();

        $response = $this->api()->json('POST', '/api/policies', [
            'account_id' => 1,
            'tenant_domain' => $policy->tenant_domain,
            'product' => Product::LOCAL_LANDING_PAGES->value,
            'policies' => [PolicyType::PAGES_HEALTH_GENERAL, PolicyType::PAGES_HEALTH_COMPREHENSIVE],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseCount('policies', 2);

        $this->assertDatabaseHas('policies', [
            'account_id' => 1,
            'tenant_domain' => $policy->tenant_domain,
            'status' => PolicyStatus::ACTIVE,
            'type' => PolicyType::PAGES_HEALTH_GENERAL,
        ]);

        $this->assertDatabaseHas('policies', [
            'account_id' => 1,
            'tenant_domain' => $policy->tenant_domain,
            'status' => PolicyStatus::ACTIVE,
            'type' => PolicyType::PAGES_HEALTH_COMPREHENSIVE,
        ]);
    }
}
