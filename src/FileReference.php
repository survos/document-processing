<?php

declare(strict_types=1);

namespace Survos\DocumentProcessing;

final readonly class FileReference
{
    public function __construct(public string $scope, public string $id)
    {
        if ('' === $id || '' === $scope) {
            throw new \InvalidArgumentException('File ID and client scope are required.');
        }
    }
}
