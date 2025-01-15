<?php declare(strict_types=1);

namespace App\Events\ComprehensiveHealth;

use App\PolicyRun;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CompletedEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public PolicyRun $policyRun;

    /**
     * Create a new event instance.
     */
    public function __construct(int $policyRunId, public readonly int $totalSubjects)
    {
        $this->policyRun = PolicyRun::find($policyRunId);
    }
}
