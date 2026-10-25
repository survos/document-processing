<?php

declare(strict_types=1);

namespace Survos\DocumentProcessing\Dto;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class OcrBlockConfidenceScores
{
    public function __construct(
        #[SerializedName('average_content_confidence_score')] public ?float $averageContentConfidenceScore = null,
        #[SerializedName('minimum_content_confidence_score')] public ?float $minimumContentConfidenceScore = null,
        #[SerializedName('block_type_confidence_score')] public ?float $blockTypeConfidenceScore = null,
    ) {
    }
}
