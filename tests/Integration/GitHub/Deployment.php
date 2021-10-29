<?php

declare(strict_types=1);

namespace PHP\Test\Actions\Integration\GitHub;

use Exception;
use PHP\Actions\Integration\GitHub\Client;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class Deployment extends TestCase
{
    protected Client $client;

    /** @test */
    public function it_should_return_created_deployment(): void
    {
        $mockResponse = new MockResponse(
            file_get_contents(__DIR__ . '/../../stubs/github/created_deployment.json'),
            ['http_code' => 201]
        );

        $this->client->setHttpClient(new MockHttpClient($mockResponse, 'https://api.github.com'));

        $deployment = new \PHP\Actions\Integration\GitHub\Deployment($this->client, 'test/test');
        $response = $deployment->create('commit-sha');

        $this->assertArrayHasKey('id', $response);
        $this->assertArrayHasKey('ref', $response);
    }

    /** @test */
    public function it_should_return_created_deployment_status(): void
    {
        $mockResponse = new MockResponse(
            file_get_contents(__DIR__ . '/../../stubs/github/created_deployment_status.json'),
            ['http_code' => 201]
        );

        $this->client->setHttpClient(new MockHttpClient($mockResponse, 'https://api.github.com'));

        $deployment = new \PHP\Actions\Integration\GitHub\Deployment($this->client, 'test/test');
        $response = $deployment->status('deployment-id', 'https://localhost');

        $this->assertArrayHasKey('id', $response);
        $this->assertArrayHasKey('deployment_url', $response);
    }

    /** @test */
    public function it_should_return_exception(): void
    {
        $mockResponse = new MockResponse(
            file_get_contents(__DIR__ . '/../../stubs/github/generic_error.json'),
            ['http_code' => 400]
        );

        $this->client->setHttpClient(new MockHttpClient($mockResponse, 'https://api.github.com'));

        $deployment = new \PHP\Actions\Integration\GitHub\Deployment($this->client, 'test/test');

        $this->expectException(Exception::class);
        $deployment->create('commit-sha');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new Client('test-token');
    }
}
