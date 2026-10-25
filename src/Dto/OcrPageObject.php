<?php

declare(strict_types=1);

namespace Survos\DocumentProcessing\Dto;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class OcrPageObject
{
    /**
     * @param OcrImageObject[]      $images
     * @param OcrTableObject[]|null $tables
     * @param string[]|null         $hyperlinks
     * @param OcrBlock[]|null       $blocks
     */
    public function __construct(
        #[SerializedName('index')] public int $index,
        #[SerializedName('markdown')] public string $markdown,
        #[SerializedName('images')] public array $images,
        #[SerializedName('dimensions')] public ?OcrPageDimensions $dimensions,
        #[SerializedName('tables')] public ?array $tables = null,
        #[SerializedName('hyperlinks')] public ?array $hyperlinks = null,
        #[SerializedName('header')] public ?string $header = null,
        #[SerializedName('footer')] public ?string $footer = null,
        #[SerializedName('confidence_scores')] public ?OcrPageConfidenceScores $confidenceScores = null,
        #[SerializedName('blocks')] public ?array $blocks = null,
    ) {
    }
}
