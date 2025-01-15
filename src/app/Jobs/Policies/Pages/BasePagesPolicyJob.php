<?php declare(strict_types=1);

namespace App\Jobs\Policies\Pages;

use App\Enums\SubjectType;
use App\Jobs\Policies\BasePolicyJob;
use Exception;
use Http;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

abstract class BasePagesPolicyJob extends BasePolicyJob
{
    private const RETRY_TIMES = 3;

    private const RETRY_SLEEP_MILLISECONDS = 100;

    protected function ping(array $subjects): Collection
    {
        $subjectsById = collect($subjects)->keyBy('id');
        $urls = $subjectsById->keys()->all();

        $responses = Http::pool(function (Pool $pool) use ($urls) {
            return collect($urls)->map(function (string $url) use ($pool) {
                return $pool
                    ->as($url)
                    ->timeout(5)
                    ->retry(self::RETRY_TIMES, self::RETRY_SLEEP_MILLISECONDS, null, false)
                    ->head($url);
            });
        });

        return collect($responses)
            ->filter(fn (Response|Exception $response) => $response instanceof Exception || $response->failed())
            ->keys()
            ->map(fn ($url) => [
                'subject_type' => SubjectType::URL,
                'subject_id' => $url,
                'meta' => Arr::get($subjectsById[$url], 'meta', []),
            ]);
    }
}
