<?php

declare(strict_types=1);

namespace Survos\DocumentProcessing\Dto;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class OcrPageDimensions
{
    public function __construct(
        #[SerializedName('dpi')] public int $dpi,
        #[SerializedName('height')] public int $height,
        #[SerializedName('width')] public int $width,
    ) {
    }
}
