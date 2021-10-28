<?php

namespace PHP\Actions\Integration\Netlify;

class Deploy
{
    protected Client $client;

    protected string $site;

    public function __construct(Client $client, $site)
    {
        $this->client = $client;
        $this->site = $site;
    }

    public function create($branch)
    {
        $body = json_encode([
            'branch' => $branch
        ], JSON_THROW_ON_ERROR);

        return $this->client->request('sites/' . $this->site . '/deploys', $body, 'POST');
    }

    public function upload($deploymentId, $file)
    {
        $body = fopen($file, 'rb');

        return $this->client->request('sites/' . $this->site . '/deploys/' . $deploymentId, $body, 'PUT', [
            'Content-Type' => 'application/zip',
        ]);
    }
}