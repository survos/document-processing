<?php

declare(strict_types=1);

namespace Survos\DocumentProcessing;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class Client
{
    public Files $files;
    public Ocr $ocr;

    public function __construct(HttpClientInterface $http, SerializerInterface $serializer, public string $name,
        string $baseUri, #[\SensitiveParameter] string $apiKey = '', float $timeout = 120)
    {
        $transport = new Transport($http, $serializer, $name, $baseUri, $apiKey, $timeout);
        $this->files = new Files($transport);
        $this->ocr = new Ocr($transport);
    }
}
