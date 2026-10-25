<?php

declare(strict_types=1);

namespace Survos\DocumentProcessing\Dto;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class OcrResponse
{
    /**
     * @param OcrPageObject[] $pages
     */
    public function __construct(
        #[SerializedName('pages')] public array $pages,
        #[SerializedName('model')] public string $model,
        #[SerializedName('usage_info')] public OcrUsageInfo $usageInfo,
        #[SerializedName('document_annotation')] public ?string $documentAnnotation = null,
    ) {
    }
}
