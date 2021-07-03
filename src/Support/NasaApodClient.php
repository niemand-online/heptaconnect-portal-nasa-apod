<?php
declare(strict_types=1);

namespace NiemandOnline\HeptaConnect\Portal\NasaApod\Support;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

class NasaApodClient
{
    private ClientInterface $client;

    private RequestFactoryInterface $requestFactory;

    private UriFactoryInterface $uriFactory;

    private string $configApiKey;

    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        UriFactoryInterface $uriFactory,
        string $configApiKey
    ) {
        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->uriFactory = $uriFactory;
        $this->configApiKey = $configApiKey;
    }

    public function getImageOfTheDay(\DateTimeInterface $day): ?array
    {
        $request = $this->requestFactory->createRequest('GET', $this->getBaseUri([
            'date' => $day->format('Y-m-d'),
        ]));
        $response = $this->client->sendRequest($request);

        if ($response->getStatusCode() < 200 || 300 <= $response->getStatusCode()) {
            return null;
        }

        return (array) \json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
    }

    public function getImage(string $url): ?ResponseInterface
    {
        $request = $this->requestFactory->createRequest('GET', $this->uriFactory->createUri($url));
        $response = $this->client->sendRequest($request);

        if ($response->getStatusCode() < 200 || 300 <= $response->getStatusCode()) {
            return null;
        }

        return $response;
    }

    protected function getBaseUri(array $params): UriInterface
    {
        return $this->uriFactory
            ->createUri('https://api.nasa.gov/planetary/apod')
            ->withQuery(\http_build_query(['api_key' => $this->configApiKey] + $params));
    }
}
