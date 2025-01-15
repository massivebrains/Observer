<?php declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PolicySummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     *
     * @phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter
     */
    public function toArray(Request $request): array
    {
        return [
            'policy' => $this->type,
            'open_incident' => $this->getOpenIncident($request),
            'most_recent_policy_run_at' => $this->policyRuns()->latest()->first()?->created_at->format('Y-m-d H:i:s'),
        ];
    }

    private function getOpenIncident(Request $request): ?array
    {
        if ($this->openIncident) {
            return (new IncidentResource($this->openIncident))->toArray($request);
        }

        return null;
    }
}
