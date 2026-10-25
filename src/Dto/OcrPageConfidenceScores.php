<?php

declare(strict_types=1);

namespace Survos\DocumentProcessing\Dto;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class OcrPageConfidenceScores
{
    /**
     * @param OcrConfidenceScore[]|null $wordConfidenceScores
     */
    public function __construct(
        #[SerializedName('average_page_confidence_score')] public float $averagePageConfidenceScore,
        #[SerializedName('minimum_page_confidence_score')] public float $minimumPageConfidenceScore,
        #[SerializedName('word_confidence_scores')] public ?array $wordConfidenceScores = null,
    ) {
    }
}
