<?php

namespace PHP\Actions\Integration\Netlify;

use RuntimeException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Client
{
    protected string $token;

    protected HttpClientInterface $httpClient;

    public function __construct($token)
    {
        $this->token = $token;

        $this->httpClient = HttpClient::create();
    }

    public function setHttpClient(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
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

        $response = $this->httpClient
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
