<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL;

use GuzzleHttp\Client as GuzzleClient;
use Ramsey\Uuid\Uuid;
use Sonnenglas\MyDHL\Exceptions\ClientException;

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
        protected bool $testMode,
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
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     * @throws ClientException
     */
    public function get(string $uri, array $query): array
    {
        return $this->request('GET', $uri, $query);
    }

    /**
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     * @throws ClientException
     */
    public function post(string $uri, array $query): array
    {
        return $this->request('POST', $uri, $query);
    }

    /**
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     * @throws ClientException
     */
    private function request(string $method, string $uri, array $query): array
    {
        $httpClient = new GuzzleClient();
        $options = $this->getRequestOptions($method, $query);

        $response = $httpClient->request($method, $uri, $options);

        /** @var array<string, mixed> $decoded */
        $decoded = json_decode((string) $response->getBody(), true, flags: JSON_THROW_ON_ERROR);

        return $decoded;
    }

    protected function generateMessageReference(): string
    {
        $this->lastMessageReference = Uuid::uuid6()->toString();

        return $this->lastMessageReference;
    }

    /**
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    protected function getRequestOptions(string $queryType, array $query): array
    {
        $requestOptions = [
            'base_uri' => $this->baseUri,
            'auth' => [$this->username, $this->password],
            'headers' => [
                'Content-Type' => 'application/json',
                'Message-Reference' => $this->generateMessageReference(),
            ],
        ];

        if ($queryType === 'GET') {
            $requestOptions['query'] = $query;
        } else {
            $requestOptions['json'] = $query;
        }

        return $requestOptions;
    }
}
