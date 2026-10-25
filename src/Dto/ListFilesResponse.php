<?php

declare(strict_types=1);

namespace Survos\DocumentProcessing\Dto;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class ListFilesResponse
{
    /**
     * @param FileSchema[] $data
     */
    public function __construct(
        #[SerializedName('data')] public array $data,
        #[SerializedName('object')] public string $object,
        #[SerializedName('total')] public ?int $total = null,
    ) {
    }
}
