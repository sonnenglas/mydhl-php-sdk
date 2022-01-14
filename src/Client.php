<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL;

use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\ResponseInterface;

class Client
{
    protected const URI_TEST = 'https://express.api.dhl.com/mydhlapi/test/';

    protected const URI_PRODUCTION = 'https://express.api.dhl.com/mydhlapi/';

    protected GuzzleClient $httpClient;

    protected string $baseUri;

    protected string $lastMessageReference;

    public function __construct(
        protected string $username,
        protected string $password,
        protected bool $testMode = false
    ) {
        $this->baseUri = $this->testMode ? self::URI_TEST : self::URI_PRODUCTION;

        $this->httpClient = new GuzzleClient($this->getHttpClientConfig());
    }

    public function getBaseUri(): string
    {
        return $this->baseUri;
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get(string $uri, array $query): ResponseInterface
    {
        return $this->httpClient->request('GET', $uri, [
            'query' => $query,
        ]);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function post(string $uri, array $query): ResponseInterface
    {
        return $this->httpClient->request('GET', $uri, [
            'query' => $query,
        ]);
    }


    protected function getHttpClientConfig(): array
    {
        return [
            'base_uri' => $this->baseUri,
            'headers' => [
                'Authorization' => $this->getAuthorizationHeader(),
                'Content-Type' => 'application/json',
                'Message-Reference' => $this->generateMessageReference(),
            ],
        ];
    }

    protected function getAuthorizationHeader(): string
    {
        return base64_encode("{$this->username}:{$this->password}");
    }

    protected function generateMessageReference(): string
    {
        $this->lastMessageReference = uniqid();

        return $this->lastMessageReference;
    }
}
