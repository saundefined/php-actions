<?php

namespace PHP\Actions\Console;

use Exception;
use PHP\Actions\Integration\GitHub\Client as GitHubClient;
use PHP\Actions\Integration\GitHub\Commit;
use PHP\Actions\Integration\GitHub\Deployment;
use PHP\Actions\Integration\GitHub\Issue;
use PHP\Actions\Integration\Netlify\Client as NetlifyClient;
use PHP\Actions\Integration\Netlify\Deploy;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Assert\Assert;

class DeployDocumentationBuildToNetlify extends Command
{
    protected static $defaultName = 'documentation:deploy';

    protected function configure()
    {
        $this
            ->setDescription('Deploys a documentation build to Netlify')
            ->addOption(
                'file',
                'f',
                InputOption::VALUE_REQUIRED,
                'Path to build zip archive'
            )
            ->addOption(
                'netlify-site',
                'ns',
                InputOption::VALUE_REQUIRED,
                'Netlify site name'
            )
            ->addOption(
                'netlify-token',
                'nt',
                InputOption::VALUE_REQUIRED,
                'An Netlify authentication token'
            )
            ->addOption(
                'repository',
                'r',
                InputOption::VALUE_REQUIRED,
                'GitHub repository'
            )
            ->addOption(
                'issue',
                'i',
                InputOption::VALUE_REQUIRED,
                'GitHub Issue/Pull request id'
            )
            ->addOption(
                'github-token',
                'gt',
                InputOption::VALUE_REQUIRED,
                'GitHub token'
            )
            ->addOption(
                'commit',
                'c',
                InputOption::VALUE_REQUIRED,
                'Commit hash'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Started...');

        $file = $input->getOption('file');
        $netlifySite = $input->getOption('netlify-site');
        $netlifyToken = $input->getOption('netlify-token');
        $repository = $input->getOption('repository');
        $issueId = $input->getOption('issue');
        $gitHubToken = $input->getOption('github-token');
        $commitHash = $input->getOption('commit');

        try {
            Assert::notEmpty($file, 'Option `file` is not specified.');
            Assert::fileExists($file, 'File `file` is not exist.');
            Assert::inArray(pathinfo($file)['extension'], ['zip'], 'File `file` is not a zip archive.');
            Assert::notEmpty($netlifySite, 'Option `netlify-site` is not specified.');
            Assert::notEmpty($netlifyToken, 'Option `netlify-token` is not specified.');
            Assert::notEmpty($repository, 'Option `repository` is not specified.');
            Assert::numeric($issueId, 'Option `issue` is numeric.');
            Assert::notEmpty($gitHubToken, 'Option `github-token` is not specified.');
            Assert::notEmpty($commitHash, 'Option `commit` is not specified.');

            $output->writeln('Assertions passed successfully');

            $gitHubClient = new GitHubClient($gitHubToken);

            $issue = new Issue($gitHubClient, $repository);
            $issue->get($issueId);

            $output->writeln('Issue exist');

            $commit = new Commit($gitHubClient, $repository);
            $commit->get($commitHash);

            $output->writeln('Commit exist');

            $netlifyClient = new NetlifyClient($netlifyToken);

            $deploy = new Deploy($netlifyClient, $netlifySite);
            $response = $deploy->create($issueId);

            $output->writeln('Netlify deploy created');

            $response = $deploy->upload($response['id'], $file);

            $output->writeln('Netlify deploy uploaded');

            $previewUrl = $response['deploy_ssl_url'];
            $issue->comment($issueId, 'Deployed on ' . $previewUrl);

            $output->writeln('GitHub comment posted');

            $deployment = new Deployment($gitHubClient, $repository);
            $response = $deployment->create($commitHash);

            $output->writeln('GitHub deployment created');

            $deployment->status($response['id'], $previewUrl);

            $output->writeln('GitHub deployment updated');
        } catch (Exception $exception) {
            $output->writeln(
                '<error>' . $exception->getMessage() . '</error>'
            );
            return Command::FAILURE;
        }

        $output->writeln('<info>Preview build is published on: ' . $previewUrl . '</info>');

        return Command::SUCCESS;
    }
}