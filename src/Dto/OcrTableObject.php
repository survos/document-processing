<?php

declare(strict_types=1);

namespace Survos\DocumentProcessing\Dto;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class OcrTableObject
{
    /**
     * @param OcrConfidenceScore[]|null $wordConfidenceScores
     */
    public function __construct(
        #[SerializedName('id')] public string $id,
        #[SerializedName('content')] public string $content,
        #[SerializedName('format')] public string $format,
        #[SerializedName('word_confidence_scores')] public ?array $wordConfidenceScores = null,
    ) {
    }
}
