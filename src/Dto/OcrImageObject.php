<?php

declare(strict_types=1);

namespace Survos\DocumentProcessing\Dto;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class OcrImageObject
{
    public function __construct(
        #[SerializedName('id')] public string $id,
        #[SerializedName('top_left_x')] public ?int $topLeftX,
        #[SerializedName('top_left_y')] public ?int $topLeftY,
        #[SerializedName('bottom_right_x')] public ?int $bottomRightX,
        #[SerializedName('bottom_right_y')] public ?int $bottomRightY,
        #[SerializedName('image_base64')] public ?string $imageBase64 = null,
        #[SerializedName('image_annotation')] public ?string $imageAnnotation = null,
    ) {
    }
}
