<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\PolicyType;
use App\Policy;
use App\PolicyRun;
use Illuminate\Console\Command;

class RunPolicyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:policy {type : The policy type to run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run policy with the given policy type';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = PolicyType::tryFrom($this->argument('type'));
        Policy::enabledFor($type)->get()->each(fn (Policy $policy) => $this->handlePolicy($policy));

        return Command::SUCCESS;
    }

    private function handlePolicy(Policy $policy): void
    {
        $policyJobClass = config("status-monitoring.policies.{$policy->type->value}");

        $policyRun = PolicyRun::create(['account_id' => $policy->account_id, 'policy_id' => $policy->id]);
        dispatch(new $policyJobClass($policyRun));

        $this->info("Dispatched job for {$policy->type->value} for account_id {$policyRun->account_id}");
    }
}
