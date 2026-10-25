<?php

declare(strict_types=1);

namespace Survos\DocumentProcessing;

final class ApiException extends \RuntimeException
{
    /** Provider payload is explicit evidence, never included in the exception message (may contain URLs). */
    public function __construct(public readonly int $status, public readonly ?string $requestId, public readonly string $responseBody)
    {
        parent::__construct(sprintf('Document processing request failed (HTTP %d).', $status), $status);
    }
}
