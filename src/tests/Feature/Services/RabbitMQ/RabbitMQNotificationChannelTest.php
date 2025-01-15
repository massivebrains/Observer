<?php declare(strict_types=1);

namespace Tests\Feature\Services\RabbitMQ;

use App\Enums\IncidentStatus;
use App\Incident;
use App\Notifications\IncidentClosedNotification;
use App\Notifications\IncidentOpenedNotification;
use App\Services\RabbitMQ\RabbitMQMessage;
use Http;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RabbitMQNotificationChannelTest extends TestCase
{
    use WithFaker;

    public function testSend()
    {
        config()->set('services.slack.notifications.bot_user_oauth_token', 'test');
        Http::fake(['*' => Http::response([])]);

        $incident = Incident::factory()->create();
        $notification = new IncidentOpenedNotification($incident);

        $this->mock('rabbitmq', function ($mock) use ($incident) {
            $message = new RabbitMQMessage($incident);

            $mock->shouldReceive('publish')
                ->once()
                ->with($message->toArray(), $message->getRoutingKey())
                ->andReturnTrue();
        });

        Notification::send($incident->policy, $notification);
    }

    public function testSendWithClosed()
    {
        config()->set('services.slack.notifications.bot_user_oauth_token', 'test');
        Http::fake(['*' => Http::response([])]);

        $incident = Incident::factory()->create([
            'closed_at' => now(),
            'status' => IncidentStatus::CLOSED,
        ]);

        $notification = new IncidentClosedNotification($incident);

        $this->mock('rabbitmq', function ($mock) use ($incident) {
            $message = new RabbitMQMessage($incident);

            $mock->shouldReceive('publish')
                ->once()
                ->with($message->toArray(), $message->getRoutingKey())
                ->andReturnTrue();
        });

        Notification::send($incident->policy, $notification);
    }
}
