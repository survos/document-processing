<?php

declare(strict_types=1);

namespace Survos\DocumentProcessing\Dto;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class OcrSignatureBlock extends OcrBlock
{
    public function __construct(
        #[SerializedName('top_left_x')] public int $topLeftX,
        #[SerializedName('top_left_y')] public int $topLeftY,
        #[SerializedName('bottom_right_x')] public int $bottomRightX,
        #[SerializedName('bottom_right_y')] public int $bottomRightY,
        #[SerializedName('content')] public string $content,
        #[SerializedName('confidence_scores')] public ?OcrBlockConfidenceScores $confidenceScores = null,
        #[SerializedName('type')] public string $type = 'signature',
    ) {
    }
}
