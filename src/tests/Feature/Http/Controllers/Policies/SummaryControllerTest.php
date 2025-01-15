<?php declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Incidents;

use App\Enums\IncidentStatus;
use App\Incident;
use App\Policy;
use App\PolicyRun;
use Tests\Feature\Http\BaseApi;

class SummaryControllerTest extends BaseApi
{
    public function testGetSuccess()
    {
        $policy1 = Policy::factory()->create();
        $policy2 = Policy::factory()->pagesHealthComprehensive()->create();

        PolicyRun::factory()->create(['policy_id' => $policy1->id]);
        $policyRun2 = PolicyRun::factory()->create(['policy_id' => $policy1->id]);

        Incident::factory()->create(['status' => IncidentStatus::CLOSED]);
        $incident2 = Incident::factory()->create();

        $data = $this->api()->json('GET', '/api/policies/summary', ['account_id' => 1])['data'];

        $this->assertEquals($policy2->type->value, $data[0]['policy']);
        $this->assertNull($data[0]['open_incident']);
        $this->assertNull($data[0]['most_recent_policy_run_at']);

        $this->assertEquals($policy1->type->value, $data[1]['policy']);
        $this->assertEquals($incident2->id, $data[1]['open_incident']['id']);
        $this->assertEquals($incident2->severity->value, $data[1]['open_incident']['severity']);
        $this->assertEquals($policyRun2->created_at->format('Y-m-d H:i:s'), $data[1]['most_recent_policy_run_at']);
    }
}
