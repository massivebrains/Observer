<?php declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Incidents;

use App\Enums\IncidentStatus;
use App\Incident;
use App\PolicyRun;
use App\PolicyRunDetail;
use Tests\Feature\Http\BaseApi;

class FailedSubjectsControllerTest extends BaseApi
{
    public function testFailedSubjectsSuccess()
    {
        $policyRun = PolicyRun::factory()->create();
        PolicyRunDetail::factory()->times(5)->create(['policy_run_id' => $policyRun->id]);
        $incident = Incident::factory()->create(['status' => IncidentStatus::CLOSED, 'open_policy_run_id' => $policyRun->id]);

        $data = $this->api()->json('GET', sprintf('/api/incidents/%d/failed-subjects', $incident->id))['data'];
        $first = $data[0];

        $this->assertArrayHasKey('id', $first);
        $this->assertArrayHasKey('subject_type', $first);
        $this->assertArrayHasKey('subject_id', $first);
        $this->assertArrayHasKey('meta', $first);
    }

    public function testFailedSubjectsSuccessWithSearch()
    {
        $policyRun = PolicyRun::factory()->create();
        PolicyRunDetail::factory()->times(5)->create(['policy_run_id' => $policyRun->id]);
        PolicyRunDetail::factory()->create(['policy_run_id' => $policyRun->id, 'meta' => ['remote_id' => 'remote-id']]);
        $incident = Incident::factory()->create(['status' => IncidentStatus::CLOSED, 'open_policy_run_id' => $policyRun->id]);

        $data = $this->api()->json('GET', sprintf('/api/incidents/%d/failed-subjects', $incident->id), ['search' => 'REMOTE-id'])['data'];
        $first = $data[0];

        $this->assertCount(1, $data);
        $this->assertArrayHasKey('id', $first);
        $this->assertArrayHasKey('subject_type', $first);
        $this->assertArrayHasKey('subject_id', $first);
        $this->assertArrayHasKey('meta', $first);
    }

    public function testFailedSubjectsSuccessWithSort()
    {
        $policyRun = PolicyRun::factory()->create();
        PolicyRunDetail::factory()->times(5)->create(['policy_run_id' => $policyRun->id]);
        PolicyRunDetail::factory()->create(['policy_run_id' => $policyRun->id, 'meta' => ['remote_id' => 'remote-id']]);
        $incident = Incident::factory()->create(['status' => IncidentStatus::CLOSED, 'open_policy_run_id' => $policyRun->id]);

        $params = [
            'sort' => 'meta.remote_id',
            'direction' => 'desc',
        ];

        $data = $this->api()->json('GET', sprintf('/api/incidents/%d/failed-subjects', $incident->id), $params)['data'];
        $meta = $data[0]['meta'];

        $this->assertCount(6, $data);
        $this->assertEquals('remote-id', $meta['remote_id']);
    }
}
