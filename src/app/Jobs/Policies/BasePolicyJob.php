<?php declare(strict_types=1);

namespace App\Jobs\Policies;

use App\PolicyRun;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

abstract class BasePolicyJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(protected PolicyRun $policyRun)
    {
    }
}
