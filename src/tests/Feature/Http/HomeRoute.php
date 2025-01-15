<?php declare(strict_types=1);

namespace Tests\Feature\Http;

use Tests\TestCase;

class HomeRoute extends TestCase
{
    /**
     * A basic test example.
     */
    public function testTheApplicationReturnsASuccessfulResponse(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
