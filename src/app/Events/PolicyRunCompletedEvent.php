<?php declare(strict_types=1);

namespace App\Events;

use App\Enums\Severity;
use App\PolicyRun;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PolicyRunCompletedEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public readonly Severity $severity;

    public readonly PolicyRun $policyRun;

    /**
     * Create a new event instance.
     */
    public function __construct(Severity $severity, PolicyRun $policyRun)
    {
        $this->severity = $severity;
        $this->policyRun = $policyRun;
    }
}
