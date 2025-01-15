<?php declare(strict_types=1);

namespace App\Services\RabbitMQ;

use App\Enums\NotificationType;
use App\Incident;

class RabbitMQMessage
{
    public function __construct(private readonly Incident $incident)
    {
        $this->incident->refresh();
    }

    public function toArray(): array
    {
        return [
            'account_id' => $this->incident->policy->account_id,
            'policy' => $this->incident->policy->type->value,
            'product' => $this->incident->policy->product->value,
            'type' => NotificationType::INCIDENT->value,
            'incident' => [
                'opened_at' => $this->incident->created_at,
                'closed_at' => $this->incident->closed_at,
                'status' => $this->incident->status->value,
                'severity' => $this->incident->severity->value,
            ],
        ];
    }

    public function getRoutingKey(): string
    {
        return sprintf(
            'notification.%s.%s',
            $this->incident->policy->product->value,
            $this->incident->policy->type->value
        );
    }
}
