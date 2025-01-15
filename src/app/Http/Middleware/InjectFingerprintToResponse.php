<?php declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\FingerprintService;
use Closure;
use Context;

/**
 * For debugging purposes, injects fingerprint of the request to the response.
 *
 * @package App\Http\Middleware
 */
class InjectFingerprintToResponse
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        $response->header(FingerprintService::HEADER_NAME, Context::get('fingerprint'));

        return $response;
    }
}
