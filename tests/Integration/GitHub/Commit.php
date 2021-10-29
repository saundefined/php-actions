<?php

declare(strict_types=1);

namespace PHP\Test\Actions\Integration\GitHub;

use Exception;
use PHP\Actions\Integration\GitHub\Client;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class Commit extends TestCase
{
    protected Client $client;

    /** @test */
    public function it_should_return_array_with_commit_data(): void
    {
        $mockResponse = new MockResponse(
            file_get_contents(__DIR__ . '/../../stubs/github/commit_info.json'),
            ['http_code' => 200]
        );

        $this->client->setHttpClient(new MockHttpClient($mockResponse, 'https://api.github.com'));

        $commit = new \PHP\Actions\Integration\GitHub\Commit($this->client, 'test/test');
        $response = $commit->get('commit-sha');

        $this->assertArrayHasKey('sha', $response);
        $this->assertArrayHasKey('commit', $response);
    }

    /** @test */
    public function it_should_return_exception(): void
    {
        $mockResponse = new MockResponse(
            file_get_contents(__DIR__ . '/../../stubs/github/generic_error.json'),
            ['http_code' => 404]
        );

        $this->client->setHttpClient(new MockHttpClient($mockResponse, 'https://api.github.com'));

        $commit = new \PHP\Actions\Integration\GitHub\Commit($this->client, 'test/test');

        $this->expectException(Exception::class);
        $commit->get('commit-sha');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new Client('test-token');
    }
}
