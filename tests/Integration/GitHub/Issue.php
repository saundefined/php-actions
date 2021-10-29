<?php

declare(strict_types=1);

namespace PHP\Test\Actions\Integration\GitHub;

use Exception;
use PHP\Actions\Integration\GitHub\Client;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class Issue extends TestCase
{
    protected Client $client;

    /** @test */
    public function it_should_return_issue_data(): void
    {
        $mockResponse = new MockResponse(
            file_get_contents(__DIR__ . '/../../stubs/github/issue_info.json'),
            ['http_code' => 200]
        );

        $this->client->setHttpClient(new MockHttpClient($mockResponse, 'https://api.github.com'));

        $issue = new \PHP\Actions\Integration\GitHub\Issue($this->client, 'test/test');
        $response = $issue->get('issue-id');

        $this->assertArrayHasKey('id', $response);
        $this->assertArrayHasKey('url', $response);
    }

    /** @test */
    public function it_should_return_posted_comment_to_issue(): void
    {
        $mockResponse = new MockResponse(
            file_get_contents(__DIR__ . '/../../stubs/github/posted_comment_to_issue.json'),
            ['http_code' => 201]
        );

        $this->client->setHttpClient(new MockHttpClient($mockResponse, 'https://api.github.com'));

        $issue = new \PHP\Actions\Integration\GitHub\Issue($this->client, 'test/test');
        $response = $issue->comment('issue-id', 'Comment posted.');

        $this->assertArrayHasKey('id', $response);
        $this->assertArrayHasKey('url', $response);
    }

    /** @test */
    public function it_should_return_exception(): void
    {
        $mockResponse = new MockResponse(
            file_get_contents(__DIR__ . '/../../stubs/github/generic_error.json'),
            ['http_code' => 400]
        );

        $this->client->setHttpClient(new MockHttpClient($mockResponse, 'https://api.github.com'));

        $issue = new \PHP\Actions\Integration\GitHub\Issue($this->client, 'test/test');

        $this->expectException(Exception::class);
        $issue->get('issue-id');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new Client('test-token');
    }
}
