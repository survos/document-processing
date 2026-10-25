<?php

declare(strict_types=1);

namespace Survos\DocumentProcessing;

/** @template T of object */
final readonly class Result
{
    /** @param T $data Raw payload is retained once, including unknown fields and explicit nulls. */
    public function __construct(public object $data, public array $raw, public ?string $requestId)
    {
    }
}
