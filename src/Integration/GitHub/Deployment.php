<?php

namespace PHP\Actions\Integration\GitHub;

class Deployment
{
    protected Client $client;

    protected string $repository;

    public function __construct(Client $client, $repository)
    {
        $this->client = $client;
        $this->repository = $repository;
    }

    public function create($commit)
    {
        $body = json_encode([
            'ref' => $commit,
            'auto_merge' => false,
            'environment' => 'preview',
            'required_contexts' => [],
        ], JSON_THROW_ON_ERROR);

        return $this->client->request('repos/' . $this->repository . '/deployments', $body, 'POST');
    }

    public function status($deploymentId, $url)
    {
        $body = json_encode([
            'state' => 'success',
            'environment_url' => $url,
        ], JSON_THROW_ON_ERROR);

        return $this->client->request(
            'repos/' . $this->repository . '/deployments/' . $deploymentId . '/statuses',
            $body,
            'POST'
        );
    }
}