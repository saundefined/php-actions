<?php

declare(strict_types=1);

namespace PHP\Test\Actions\Console;

use PHP\Actions\Integration\GitHub\Client as GitHubClient;
use PHP\Actions\Integration\Netlify\Client as NetlifyClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class DeployDocumentationBuildToNetlify extends TestCase
{
    protected CommandTester $command;

    /** @test */
    public function it_should_return_error_not_file()
    {
        $this->command->execute([]);
        $this->assertEquals(
            implode(PHP_EOL, ['Started...', 'Option `file` is not specified.']),
            trim($this->command->getDisplay())
        );
    }

    /** @test */
    public function it_should_return_error_wrong_file()
    {
        $this->command->execute([
            '--file' => __DIR__ . '/../stubs/netlify/created_deploy.json'
        ]);
        $this->assertEquals(
            implode(PHP_EOL, ['Started...', 'File `file` is not a zip archive.']),
            trim($this->command->getDisplay())
        );
    }

    /** @test */
    public function it_should_return_error_not_netlify_site()
    {
        $this->command->execute([
            '--file' => __DIR__ . '/../stubs/files/sample.zip'
        ]);
        $this->assertEquals(
            implode(PHP_EOL, ['Started...', 'Option `netlify-site` is not specified.']),
            trim($this->command->getDisplay())
        );
    }

    /** @test */
    public function it_should_return_error_not_netlify_token()
    {
        $this->command->execute([
            '--file' => __DIR__ . '/../stubs/files/sample.zip',
            '--netlify-site' => 'netlify-site',
        ]);
        $this->assertEquals(
            implode(PHP_EOL, ['Started...', 'Option `netlify-token` is not specified.']),
            trim($this->command->getDisplay())
        );
    }

    /** @test */
    public function it_should_return_error_not_repository()
    {
        $this->command->execute([
            '--file' => __DIR__ . '/../stubs/files/sample.zip',
            '--netlify-site' => 'netlify-site',
            '--netlify-token' => 'netlify-token',
        ]);
        $this->assertEquals(
            implode(PHP_EOL, ['Started...', 'Option `repository` is not specified.']),
            trim($this->command->getDisplay())
        );
    }

    /** @test */
    public function it_should_return_error_not_issue()
    {
        $this->command->execute([
            '--file' => __DIR__ . '/../stubs/files/sample.zip',
            '--netlify-site' => 'netlify-site',
            '--netlify-token' => 'netlify-token',
            '--repository' => 'repository/name',
        ]);
        $this->assertEquals(
            implode(PHP_EOL, ['Started...', 'Option `issue` is not a numeric.']),
            trim($this->command->getDisplay())
        );
    }

    /** @test */
    public function it_should_return_error_not_github_token()
    {
        $this->command->execute([
            '--file' => __DIR__ . '/../stubs/files/sample.zip',
            '--netlify-site' => 'netlify-site',
            '--netlify-token' => 'netlify-token',
            '--repository' => 'repository/name',
            '--issue' => 1,
        ]);
        $this->assertEquals(
            implode(PHP_EOL, ['Started...', 'Option `github-token` is not specified.']),
            trim($this->command->getDisplay())
        );
    }

    /** @test */
    public function it_should_return_error_not_commit()
    {
        $this->command->execute([
            '--file' => __DIR__ . '/../stubs/files/sample.zip',
            '--netlify-site' => 'netlify-site',
            '--netlify-token' => 'netlify-token',
            '--repository' => 'repository/name',
            '--issue' => 1,
            '--github-token' => 'github-token',
        ]);
        $this->assertEquals(
            implode(PHP_EOL, ['Started...', 'Option `commit` is not specified.']),
            trim($this->command->getDisplay())
        );
    }

    /** @test */
    public function it_should_test_a_command_workflow()
    {
        $this->command->execute([
            '--file' => __DIR__ . '/../stubs/files/sample.zip',
            '--netlify-site' => 'netlify-site',
            '--netlify-token' => 'netlify-token',
            '--repository' => 'repository/name',
            '--issue' => 1,
            '--github-token' => 'github-token',
            '--commit' => 'commit',
        ]);

        $this->assertEquals(
            implode(PHP_EOL, [
                'Started...',
                'Assertions passed successfully',
                'Issue exist',
                'Commit exist',
                'Netlify deploy created',
                'Netlify deploy uploaded',
                'GitHub comment posted',
                'GitHub deployment created',
                'GitHub deployment updated',
                'Preview build is published on: string'
            ]),
            trim($this->command->getDisplay())
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $gitHubClient = new GitHubClient('test-token');
        $gitHubClient->setHttpClient(
            new MockHttpClient([
                new MockResponse(
                    file_get_contents(__DIR__ . '/../stubs/github/issue_info.json'),
                    ['http_code' => 200]
                ),
                new MockResponse(
                    file_get_contents(__DIR__ . '/../stubs/github/commit_info.json'),
                    ['http_code' => 200]
                ),
                new MockResponse(
                    file_get_contents(__DIR__ . '/../stubs/github/posted_comment_to_issue.json'),
                    ['http_code' => 201]
                ),
                new MockResponse(
                    file_get_contents(__DIR__ . '/../stubs/github/created_deployment.json'),
                    ['http_code' => 201]
                ),
                new MockResponse(
                    file_get_contents(__DIR__ . '/../stubs/github/created_deployment_status.json'),
                    ['http_code' => 201]
                )
            ], 'https://api.github.com')
        );

        $netlifyClient = new NetlifyClient('test-token');
        $netlifyClient->setHttpClient(
            new MockHttpClient([
                new MockResponse(
                    file_get_contents(__DIR__ . '/../stubs/netlify/created_deploy.json'),
                    ['http_code' => 200]
                ),
                new MockResponse(
                    file_get_contents(__DIR__ . '/../stubs/netlify/uploaded_deploy.json'),
                    ['http_code' => 200]
                ),
            ], 'https://api.netlify.com')
        );

        $this->command = new CommandTester(
            new \PHP\Actions\Console\DeployDocumentationBuildToNetlify(null, $gitHubClient, $netlifyClient)
        );
    }
}
