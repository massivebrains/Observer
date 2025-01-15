<?php declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Incidents;

use App\Http\Resources\IncidentCollection;
use App\Incident;
use Carbon\Carbon;
use Tests\Feature\Http\BaseApi;

class GetControllerTest extends BaseApi
{
    public function testGetSuccess()
    {
        [$incident1, $incident2] = Incident::factory()->count(2)->create();

        $data = $this->api()->json('GET', '/api/incidents', ['account_id' => 1])['data'];

        $this->assertEquals($incident1->id, $data[0]['id']);
        $this->assertEquals($incident1->status->value, $data[0]['status']);
        $this->assertEquals($incident1->created_at->format('Y-m-d H:i:s'), $data[0]['created_at']);
        $this->assertEquals($incident1->policy->type->value, $data[0]['policy']);
        $this->assertEquals($incident1->severity->value, $data[0]['severity']);

        $this->assertEquals($incident2->id, $data[1]['id']);
        $this->assertEquals($incident2->status->value, $data[1]['status']);
        $this->assertEquals($incident2->created_at, $data[1]['created_at']);
        $this->assertEquals($incident2->policy->type->value, $data[1]['policy']);
        $this->assertEquals($incident2->severity->value, $data[1]['severity']);
    }

    public function testSortByCreatedAtAsc()
    {
        $incident1 = Incident::factory()->create(['created_at' => Carbon::now()->subDays(5)]);
        $incident2 = Incident::factory()->create(['created_at' => Carbon::now()->subDays(3)]);
        $incident3 = Incident::factory()->create(['created_at' => Carbon::now()->subDays(4)]);

        $data = $this->api()->json('GET', '/api/incidents', [
            'account_id' => 1,
            'sort' => 'created_at',
            'direction' => 'desc',
        ])['data'];

        $this->assertEquals($incident2->id, $data[0]['id']);
        $this->assertEquals($incident3->id, $data[1]['id']);
        $this->assertEquals($incident1->id, $data[2]['id']);
    }

    // Just for coverage
    public function testIncidentCollection()
    {
        $collection = new IncidentCollection(Incident::factory()->count(2)->create());
        $this->assertArrayHasKey('data', $collection->response()->getData(true));
    }
}
