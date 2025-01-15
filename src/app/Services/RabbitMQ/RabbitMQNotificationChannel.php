<?php declare(strict_types=1);

namespace App\Services\RabbitMQ;

use Illuminate\Notifications\Notification;

class RabbitMQNotificationChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        /** @var RabbitMQMessage $message */
        $message = $notification->toRabbitMQ($notifiable);

        app('rabbitmq')->publish($message->toArray(), $message->getRoutingKey());
    }
}
