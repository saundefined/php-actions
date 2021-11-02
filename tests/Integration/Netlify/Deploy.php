<?php

declare(strict_types=1);

namespace PHP\Test\Actions\Integration\Netlify;

use Exception;
use PHP\Actions\Integration\Netlify\Client;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use ZipArchive;

class Deploy extends TestCase
{
    protected Client $client;

    protected string $zipFile;

    /** @test */
    public function it_should_return_created_deploy(): void
    {
        $mockResponse = new MockResponse(
            file_get_contents(__DIR__ . '/../../stubs/netlify/created_deploy.json'),
            ['http_code' => 200]
        );

        $this->client->setHttpClient(new MockHttpClient($mockResponse, 'https://api.netlify.com'));

        $deploy = new \PHP\Actions\Integration\Netlify\Deploy($this->client, 'php-doc.netlify.app');
        $response = $deploy->create('branch-name');

        $this->assertArrayHasKey('id', $response);
    }

    /** @test */
    public function it_should_return_uploaded_deploy(): void
    {
        $mockResponse = new MockResponse(
            file_get_contents(__DIR__ . '/../../stubs/netlify/uploaded_deploy.json'),
            ['http_code' => 200]
        );

        $this->client->setHttpClient(new MockHttpClient($mockResponse, 'https://api.netlify.com'));

        $deploy = new \PHP\Actions\Integration\Netlify\Deploy($this->client, 'php-doc.netlify.app');
        $response = $deploy->upload('deployment-id', $this->zipFile);

        $this->assertArrayHasKey('id', $response);
    }

    /** @test */
    public function it_should_return_exception(): void
    {
        $mockResponse = new MockResponse(
            file_get_contents(__DIR__ . '/../../stubs/netlify/generic_error.json'),
            ['http_code' => 404]
        );

        $this->client->setHttpClient(new MockHttpClient($mockResponse, 'https://api.netlify.com'));

        $deploy = new \PHP\Actions\Integration\Netlify\Deploy($this->client, 'php-doc.netlify.app');

        $this->expectException(Exception::class);
        $deploy->create('branch-name');
    }

    /** @test */
    public function it_should_return_exception_on_empty_response(): void
    {
        $mockResponse = new MockResponse(
            '',
            ['http_code' => 404]
        );

        $this->client->setHttpClient(new MockHttpClient($mockResponse, 'https://api.netlify.com'));

        $deploy = new \PHP\Actions\Integration\Netlify\Deploy($this->client, 'php-doc.netlify.app');

        $this->expectException(Exception::class);
        $deploy->create('branch-name');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new Client('test-token');

        $this->zipFile = tempnam(sys_get_temp_dir(), 'temp');
        rename($this->zipFile, $this->zipFile .= '.zip');

        $zip = new ZipArchive();
        $zip->open($this->zipFile, ZipArchive::OVERWRITE);
        $zip->addFile(__DIR__ . '/../../stubs/netlify/uploaded_deploy.json');
        $zip->close();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unlink($this->zipFile);
    }
}
