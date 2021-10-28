<?php

namespace PHP\Actions\Integration\GitHub;

use RuntimeException;
use Symfony\Component\HttpClient\HttpClient;

class Client
{
    protected string $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function request($endpoint, $body = null, $httpMethod = 'GET')
    {
        $options = [
            'headers' => [
                'Accept' => 'application/vnd.github.v3+json',
                'Authorization' => 'token ' . $this->token,
            ],
        ];

        if ($body) {
            $options['body'] = $body;
        }

        $response = HttpClient::create()
            ->request(
                $httpMethod,
                'https://api.github.com/' . $endpoint,
                $options
            );

        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            return json_decode(
                $response->getContent(false),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        }

        throw new RuntimeException('Invalid GitHub response: ' . $endpoint . ' ' . $response->getContent(false));
    }
}