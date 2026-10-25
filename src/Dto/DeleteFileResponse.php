<?php

declare(strict_types=1);

namespace Survos\DocumentProcessing\Dto;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class DeleteFileResponse
{
    public function __construct(
        #[SerializedName('id')] public string $id,
        #[SerializedName('object')] public string $object,
        #[SerializedName('deleted')] public bool $deleted,
    ) {
    }
}
