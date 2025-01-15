<?php declare(strict_types=1);

namespace App\Listeners;

use App\Enums\Severity;
use App\Events\PolicyRunCompletedEvent;
use App\Incident;
use App\Notifications\IncidentClosedNotification;
use App\Notifications\IncidentOpenedNotification;
use App\PolicyRun;
use Notification;

class IncidentListener
{
    private Severity $severity;

    private PolicyRun $policyRun;

    /**
     * Handle the event.
     */
    public function handle(PolicyRunCompletedEvent $event): void
    {
        $this->severity = $event->severity;
        $this->policyRun = $event->policyRun;

        if ($this->severity === Severity::NONE) {
            $this->handleSuccessfulPolicyRun();
        } else {
            $this->handleFailedPolicyRun();
        }
    }

    private function handleFailedPolicyRun()
    {
        if (Incident::openFor($this->policyRun->policy)->exists()) {
            return;
        }

        $incident = $this->policyRun->policy->incidents()->create([
            'account_id' => $this->policyRun->account_id,
            'open_policy_run_id' => $this->policyRun->id,
            'severity' => $this->severity,
        ]);

        Notification::send($this->policyRun->policy, new IncidentOpenedNotification($incident));
    }

    private function handleSuccessfulPolicyRun()
    {
        $incident = Incident::openFor($this->policyRun->policy)->first();
        if ($incident) {
            $incident->close($this->policyRun);
            Notification::send($this->policyRun->policy, new IncidentClosedNotification($incident));
        }
    }
}
