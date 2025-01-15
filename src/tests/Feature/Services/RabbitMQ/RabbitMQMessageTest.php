<?php declare(strict_types=1);

namespace Tests\Feature\Services\RabbitMQ;

use App\Enums\NotificationType;
use App\Incident;
use App\Services\RabbitMQ\RabbitMQMessage;
use Tests\TestCase;

class RabbitMQMessageTest extends TestCase
{
    public function testToArray()
    {
        $incident = Incident::factory()->create();

        $this->assertEquals([
            'account_id' => $incident->policy->account_id,
            'policy' => $incident->policy->type->value,
            'product' => $incident->policy->product->value,
            'type' => NotificationType::INCIDENT->value,
            'incident' => [
                'opened_at' => $incident->created_at,
                'closed_at' => $incident->closed_at,
                'status' => $incident->status->value,
                'severity' => $incident->severity->value,
            ],
        ], (new RabbitMQMessage($incident))->toArray());
    }

    public function testRoutingKey()
    {
        $incident = Incident::factory()->create();

        $this->assertEquals('notification.LOCAL_LANDING_PAGES.PAGES_HEALTH_GENERAL', (new RabbitMQMessage($incident))->getRoutingKey());
    }
}
