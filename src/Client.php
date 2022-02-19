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
     * @throws ClientException
     */
    public function get(string $uri, array $query): array
    {
        $httpClient = new GuzzleClient();

        $options = $this->getRequestOptions('GET', $query);

        $response = $httpClient->request('GET', $uri, $options);

        return json_decode((string) $response->getBody(), true);
    }


    /**
     * @throws ClientException
     */
    public function post(string $uri, array $query): array
    {
        $httpClient = new GuzzleClient();

        $options = $this->getRequestOptions('POST', $query);

        $response = $httpClient->request('POST', $uri, $options);

        return json_decode((string) $response->getBody(), true);
    }


    protected function generateMessageReference(): string
    {
        $this->lastMessageReference = Uuid::uuid6()->toString();

        return $this->lastMessageReference;
    }

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

        if ($queryType === "GET") {
            $requestOptions['query'] = $query;
        } else {
            $requestOptions['json'] = $query;
        }

        return $requestOptions;
    }
}
