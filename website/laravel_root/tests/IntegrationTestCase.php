<?php

namespace Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

abstract class IntegrationTestCase extends TestCase
{
    protected $client;
    protected static $migrated = false;

    protected function setUp(): void
    {
        parent::setUp();

        // For each test, create a new client with refreshed cookiejar
        $this->client = new Client([
            'base_uri' => env('APP_URL', 'https://localhost'),
            'verify' => false,
            'cookies' => new CookieJar(),
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }


    /**
     * HTTP request builder for integration tests.
     */
    protected function httpRequestBuilder()
    {
        return new class($this->client) {
            private $client;
            private $pendingRequest = [];
            private $xsrfToken = null;

            public function __construct($client)
            {
                $this->client = $client;
            }

            public function get($uri, $params = [])
            {
                $this->pendingRequest = [
                    'method' => 'GET',
                    'uri' => $uri,
                    'params' => $params,
                ];
                return $this;
            }

            public function post($uri, $params = [])
            {
                $this->pendingRequest = [
                    'method' => 'POST',
                    'uri' => $uri,
                    'params' => $params,
                ];
                return $this;
            }

            public function patch($uri, $params = [])
            {
                $this->pendingRequest = [
                    'method' => 'PATCH',
                    'uri' => $uri,
                    'params' => $params,
                ];
                return $this;
            }

            public function put($uri, $params = [])
            {
                $this->pendingRequest = [
                    'method' => 'PUT',
                    'uri' => $uri,
                    'params' => $params,
                ];
                return $this;
            }

            public function delete($uri, $params = [])
            {
                $this->pendingRequest = [
                    'method' => 'DELETE',
                    'uri' => $uri,
                    'params' => $params,
                ];
                return $this;
            }

            private function getXsrfToken()
            {
                // Use lightweight route to set CSRF token cookie
                $response = $this->get('/test/csrf-token')->send();
                
                $data = json_decode($response->getBody(), true);

                return $data['csrf_token'] ?? null;
            }

            public function withXsrf()
            {
                // Get xsrf token only when it doesn't exist yet to avoid making unnecessary HTTP requests.
                $this->xsrfToken = $this->getXsrfToken();
                return $this;
            }

            public function actingAs($user, $password = 'password')
            {
                // Log in the user
                $response = $this->withXsrf()->post('/login', [
                    'email' => $user->email,
                    'password' => $password,
                ])->send();

                if ($response->getStatusCode() !== 302) {
                    throw new \Exception('Login failed for user: ' . $user->email);
                }

                // Get new xsrf token after authentication
                $this->xsrfToken = $this->getXsrfToken();

                return $this;
            }

            public function send()
            {
                if (!isset($this->pendingRequest['method']) || !isset($this->pendingRequest['uri'])) {
                    throw new \Exception('HTTP method and URI must be set before calling getResponse().');
                }

                $method = $this->pendingRequest['method'];
                $uri = $this->pendingRequest['uri'];
                $params = $this->pendingRequest['params'] ?? [];

                $options = [
                    'allow_redirects' => false,
                ];

                if (in_array($method, ['POST', 'PATCH', 'PUT', 'DELETE'])) {
                    $options['form_params'] = $params;
                } else {
                    $options['query'] = $params;
                }

                // Always send the x-testing header
                $options['headers']['X-TESTING'] = app()->environment('testing');

                if ($this->xsrfToken) {
                    $options['headers']['X-CSRF-TOKEN'] = $this->xsrfToken;
                }

                $response = $this->client->request($method, $uri, $options);

                // Reset only the builder's request
                $this->pendingRequest = [];

                return $response;
            }

            public function __invoke()
            {
                return $this->send();
            }
        };
    }
} 