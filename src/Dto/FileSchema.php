<?php

declare(strict_types=1);

namespace Survos\DocumentProcessing\Dto;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class FileSchema
{
    public function __construct(
        #[SerializedName('id')] public string $id,
        #[SerializedName('object')] public string $object,
        #[SerializedName('bytes')] public int $bytes,
        #[SerializedName('created_at')] public int $createdAt,
        #[SerializedName('filename')] public string $filename,
        #[SerializedName('purpose')] public string $purpose,
        #[SerializedName('sample_type')] public string $sampleType,
        #[SerializedName('source')] public string $source,
        #[SerializedName('num_lines')] public ?int $numLines = null,
        #[SerializedName('mimetype')] public ?string $mimetype = null,
        #[SerializedName('signature')] public ?string $signature = null,
        #[SerializedName('expires_at')] public ?int $expiresAt = null,
        #[SerializedName('visibility')] public ?string $visibility = null,
    ) {
    }
}
