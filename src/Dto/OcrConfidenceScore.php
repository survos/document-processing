<?php

declare(strict_types=1);

namespace Survos\DocumentProcessing\Dto;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class OcrConfidenceScore
{
    public function __construct(
        #[SerializedName('text')] public string $text,
        #[SerializedName('confidence')] public float $confidence,
        #[SerializedName('start_index')] public int $startIndex,
    ) {
    }
}
