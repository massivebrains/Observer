<?php declare(strict_types=1);

namespace Tests\Feature\Http;

use Tests\TestCase;

class BaseApi extends TestCase
{
    public function api()
    {
        return $this->withHeader('Authorization', sprintf('Bearer %s', config('status-monitoring.auth-token')));
    }
}
