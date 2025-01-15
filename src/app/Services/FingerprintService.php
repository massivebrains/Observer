<?php declare(strict_types=1);

namespace App\Services;

use Str;

/**
 * This class is responsible for fingerprint generation, it gets carried by Context.
 * We either get that from the client to be able to track requests from other services or generate randomly.
 * We do not use Request::fingerprint() to not have duplicate fingerprints from the same client.
 */
class FingerprintService
{
    protected string $fingerprint;

    public const HEADER_NAME = 'X-Fingerprint';

    private const LOCAL_FINGERPRINT_LENGTH = 20;

    /**
     * It's 40 for request because we try to support process_id coming from monolith (36 characters).
     */
    private const REMOTE_FINGERPRINT_MAX_LENGTH = 40;

    public function __construct()
    {
        $this->fingerprint = self::getFromRequest() ?: Str::random(self::LOCAL_FINGERPRINT_LENGTH);
    }

    public function get(): string
    {
        return $this->fingerprint;
    }

    /**
     * Try to grab fingerprint from the request.
     * We're not savages, we sanitize it first.
     *
     * @return string|null
     */
    protected static function getFromRequest(): ?string
    {
        $request = request();

        if (!$request->hasHeader(self::HEADER_NAME)) {
            return null;
        }

        $fingerprint = $request->header(self::HEADER_NAME, '');
        $fingerprint = preg_replace('/[^a-zA-Z0-9\-]/', '', $fingerprint);
        $fingerprint = Str::limit($fingerprint, self::REMOTE_FINGERPRINT_MAX_LENGTH);

        return $fingerprint;
    }
}
