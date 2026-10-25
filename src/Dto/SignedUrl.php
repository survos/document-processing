<?php

declare(strict_types=1);

namespace Survos\DocumentProcessing\Dto;

final readonly class SignedUrl
{
    public function __construct(public string $url)
    {
    }
}
