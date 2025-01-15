<?php declare(strict_types=1);

namespace App\Notifications;

use App\Incident;
use App\Policy;
use App\Services\RabbitMQ\RabbitMQMessage;
use App\Services\RabbitMQ\RabbitMQNotificationChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Slack\BlockKit\Blocks\SectionBlock;
use Illuminate\Notifications\Slack\SlackMessage;

class IncidentClosedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public readonly Incident $incident)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(): array
    {
        return ['slack', RabbitMQNotificationChannel::class];
    }

    /**
     * Get the Slack representation of the notification.
     */
    public function toSlack(Policy $notifiable): SlackMessage
    {
        return (new SlackMessage())
            ->headerBlock(sprintf('Incident closed for Account ID %s :verify:', $notifiable->account_id))
            ->sectionBlock(function (SectionBlock $block) use ($notifiable) {
                $block->field(sprintf("*Opened:*\n%s", $this->incident->created_at->diffForHumans()))->markdown();
                $block->field(sprintf("*Severity:*\n%s", $this->incident->formattedSeverity))->markdown();
                $block->field(sprintf("*Policy:*\n%s", $notifiable->formattedType))->markdown();
                $block->field(sprintf("*Product:*\n%s", $notifiable->product->value))->markdown();
            });
    }

    public function toRabbitMQ(): RabbitMQMessage
    {
        return new RabbitMQMessage($this->incident);
    }
}
