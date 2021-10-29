<?php

namespace PHP\Actions\Integration\GitHub;

class Issue
{
    protected Client $client;
    protected string $repository;

    public function __construct(Client $client, $repository)
    {
        $this->client = $client;
        $this->repository = $repository;
    }

    public function get($issueId)
    {
        return $this->client->request('repos/' . $this->repository . '/issues/' . $issueId);
    }

    public function comment($issueId, $body)
    {
        $body = json_encode([
            'body' => $body
        ], JSON_THROW_ON_ERROR);

        return $this->client->request(
            'repos/' . $this->repository . '/issues/' . $issueId . '/comments',
            $body,
            'POST'
        );
    }
}
