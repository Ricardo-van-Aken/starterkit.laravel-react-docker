<?php

namespace Tests\Integration;

use Tests\IntegrationTestCase;

class ExampleTest extends IntegrationTestCase
{
    public function test_returns_a_successful_response()
    {
        $response = $this->httpRequestBuilder()->get('/')->send();

        $this->assertEquals(200, $response->getStatusCode());
    }
} 