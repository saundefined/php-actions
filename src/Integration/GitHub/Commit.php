<?php

namespace PHP\Actions\Integration\GitHub;

class Commit
{
    protected Client $client;

    protected string $repository;

    public function __construct(Client $client, $repository)
    {
        $this->client = $client;
        $this->repository = $repository;
    }

    public function get($commit)
    {
        return $this->client->request('repos/' . $this->repository . '/commits/' . $commit);
    }
}
