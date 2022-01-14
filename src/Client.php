<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL;

use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\ResponseInterface;

class Client
{
    protected const URI_PRODUCTION = 'https://express.api.dhl.com/mydhlapi/';

    protected const URI_MOCK = 'https://api-mock.dhl.com/mydhlapi/';

    protected const URI_TEST = 'https://express.api.dhl.com/mydhlapi/test/';

    protected string $baseUri;

    protected string $lastMessageReference;

    public function __construct(
        protected string $username,
        protected string $password,
        protected bool $testMode
    ) {
        $this->baseUri = $this->testMode ? self::URI_TEST : self::URI_PRODUCTION;
    }

    public function enableMockServer(): void
    {
        $this->baseUri = self::URI_MOCK;
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
        $httpClient = new GuzzleClient($this->getHttpClientConfig());

        return $httpClient->request('GET', $uri, $this->getRequestOptions($query));
    }


    protected function getHttpClientConfig(): array
    {
        return [
            'base_uri' => $this->baseUri,
            'headers' => [
                'Authorization' => $this->getAuthorizationHeader(),
                'Content-Type' => 'application/json',

            ],
        ];
    }

    protected function getAuthorizationHeader(): string
    {
        $token = base64_encode("{$this->username}:{$this->password}");

        return "Basic $token";
    }

    protected function generateMessageReference(): string
    {
        $this->lastMessageReference = uniqid();

        return $this->lastMessageReference;
    }

    protected function getRequestOptions(array $query): array
    {
        return [
            'query' => $query,
            'headers' => [
                'Message-Reference' => $this->generateMessageReference(),
            ],
        ];
    }
}
