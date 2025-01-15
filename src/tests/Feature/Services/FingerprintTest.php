<?php declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Services\FingerprintService;
use Context;
use Illuminate\Support\Facades\Log;
use Monolog\Handler\TestHandler;
use Monolog\Logger as MonologLogger;
use Request;
use Str;
use Tests\TestCase;

class FingerprintTest extends TestCase
{
    public function testApplicationReturnFingerprintOnTheResponse(): void
    {
        $response = $this->get('/');
        $fingerprint = Context::get('fingerprint');

        $response->assertHeader(FingerprintService::HEADER_NAME);

        $this->assertEquals($fingerprint, $response->headers->get(FingerprintService::HEADER_NAME));
    }

    public function testFingerprintServiceUsesRequestHeader(): void
    {
        $requestFingerprint = Str::random(36);
        $this->app->instance('request', Request::create('/', 'GET', [], [], [], ['HTTP_X-Fingerprint' => $requestFingerprint]));
        $fs = new FingerprintService();

        $fingerprint = $fs->get();

        $this->assertEquals($requestFingerprint, $fingerprint);
    }

    public function testApplicationAddFingerprintToLogs(): void
    {
        $fingerprint = Context::get('fingerprint');
        $testHandler = new TestHandler();

        /** @var MonologLogger $logger */
        $logger = Log::getLogger();
        $logger->pushHandler($testHandler);

        Log::info('test');

        $records = $testHandler->getRecords();

        $this->assertNotEmpty($fingerprint);
        $this->assertNotEmpty($records);
        $this->assertEquals($fingerprint, $records[0]['extra']['fingerprint']);
    }
}
