<?php

declare(strict_types=1);

namespace Survos\DocumentProcessing\Dto;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class OcrUsageInfo
{
    public function __construct(
        #[SerializedName('pages_processed')] public int $pagesProcessed,
        #[SerializedName('doc_size_bytes')] public ?int $docSizeBytes = null,
    ) {
    }
}
