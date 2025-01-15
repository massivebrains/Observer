<?php declare(strict_types=1);

namespace App\Services\Monolith;

use App\Exceptions\MonolithApiException;
use App\Policy;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class MonolithHttpService
{
    private readonly PendingRequest $http;

    public function __construct(public readonly string $token)
    {
        $this->http = Http::withToken($token)->retry(3, 1000, null, false);
    }

    public function getGeneralHealthSubjects(Policy $policy): array
    {
        $url = sprintf('https://%s/api/status_monitoring/0/get_pages_health_general_subjects', $policy->tenant_domain);
        $response = $this->http->get($url, ['account_id' => $policy->account_id]);
        if ($response->failed()) {
            throw new MonolithApiException((string) $response->status());
        }

        return Arr::get($response->json(), 'data.subjects', []);
    }

    public function getComprehensiveHealthMeta(Policy $policy): array
    {
        $response = $this->http->get(sprintf('https://%s/comprehensive', $policy->tenant_domain));
        $url = sprintf('https://%s/api/status_monitoring/0/get_pages_health_comprehensive_subjects', $policy->tenant_domain);
        $response = $this->http->get($url, ['account_id' => $policy->account_id]);
        if ($response->failed()) {
            throw new MonolithApiException((string) $response->status());
        }

        return Arr::get($response->json(), 'data.meta', []);
    }

    public function getComprehensiveHealthSubjects(Policy $policy, int $pageNumber = 1, int $perPage = 100): array
    {
        $url = sprintf('https://%s/api/status_monitoring/0/get_pages_health_comprehensive_subjects', $policy->tenant_domain);
        $response = $this->http->get($url, ['account_id' => $policy->account_id, 'page' => $pageNumber, 'per_page' => $perPage]);
        if ($response->failed()) {
            throw new MonolithApiException((string) $response->status());
        }

        return Arr::get($response->json(), 'data.subjects', []);
    }
}
