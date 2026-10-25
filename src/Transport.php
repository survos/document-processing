<?php

declare(strict_types=1);

namespace Survos\DocumentProcessing;

use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final readonly class Transport
{
    private HttpClientInterface $http;
    public string $scope;

    public function __construct(HttpClientInterface $http, private SerializerInterface $serializer, public string $name,
        private string $baseUri, #[\SensitiveParameter] string $apiKey = '', float $timeout = 120)
    {
        $parts = parse_url($baseUri);
        if (!$parts || !in_array($parts['scheme'] ?? '', ['http', 'https'], true) || empty($parts['host'])
            || isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment']) || $timeout <= 0) {
            throw new \InvalidArgumentException('A full HTTP(S) base URI without credentials, query or fragment and a positive timeout are required.');
        }
        $this->http = $http->withOptions(['timeout' => $timeout, 'max_redirects' => 0,
            'headers' => ['Authorization' => '' === $apiKey ? '' : 'Bearer '.$apiKey, 'Accept' => 'application/json']]);
        $this->scope = hash('sha256', $name."\0".rtrim($baseUri, '/')."\0".$apiKey);
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $type
     *
     * @return Result<T>
     */
    public function json(string $method, string $path, string $type, array $options = []): Result
    {
        $response = $this->request($method, $path, $options);
        $body = $response->getContent(false);
        $headers = $response->getHeaders(false);
        $requestId = $headers['x-request-id'][0] ?? null;
        $this->check($response, $body);
        $data = $this->serializer->deserialize($body, $type, 'json', [AbstractNormalizer::REQUIRE_ALL_PROPERTIES => true]);
        if (!$data instanceof $type) {
            throw new \UnexpectedValueException('Serializer returned an unexpected response type.');
        }

        return new Result($data, json_decode($body, true, 512, JSON_THROW_ON_ERROR), $requestId);
    }

    public function request(string $method, string $path, array $options = []): ResponseInterface
    {
        return $this->http->request($method, rtrim($this->baseUri, '/').'/'.ltrim($path, '/'), $options);
    }

    /** @return \Generator<string> Keep/close the generator to release a partially consumed download. */
    public function download(string $path): \Generator
    {
        $response = $this->request('GET', $path, ['buffer' => false]);
        try {
            $this->check($response);
            foreach ($this->http->stream($response) as $chunk) {
                yield $chunk->getContent();
            }
        } finally {
            $response->cancel();
        }
    }

    private function check(ResponseInterface $response, ?string $body = null): void
    {
        $status = $response->getStatusCode();
        if ($status < 200 || $status >= 300) {
            throw new ApiException($status, $response->getHeaders(false)['x-request-id'][0] ?? null, $body ?? $response->getContent(false));
        }
    }
}
