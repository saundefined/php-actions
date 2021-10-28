<?php

namespace PHP\Actions\Integration\Netlify;

use RuntimeException;
use Symfony\Component\HttpClient\HttpClient;

class Client
{
    protected string $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function request($endpoint, $body = null, $httpMethod = 'GET', $headers = [])
    {
        $options = [
            'headers' => array_merge([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->token,
            ], $headers),
        ];

        if ($body) {
            $options['body'] = $body;
        }

        $response = HttpClient::create()
            ->request(
                $httpMethod,
                'https://api.netlify.com/api/v1/' . $endpoint,
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

        throw new RuntimeException('Invalid Netlify response: ' . $endpoint . ' ' . $response->getContent(false));
    }
}