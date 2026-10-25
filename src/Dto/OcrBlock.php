<?php

declare(strict_types=1);

namespace Survos\DocumentProcessing\Dto;

use Symfony\Component\Serializer\Attribute\DiscriminatorMap;

#[DiscriminatorMap(typeProperty: 'type', mapping: [
    'aside_text' => OcrAsideTextBlock::class,
    'caption' => OcrCaptionBlock::class,
    'code' => OcrCodeBlock::class,
    'equation' => OcrEquationBlock::class,
    'footer' => OcrFooterBlock::class,
    'header' => OcrHeaderBlock::class,
    'image' => OcrImageBlock::class,
    'list' => OcrListBlock::class,
    'references' => OcrReferencesBlock::class,
    'signature' => OcrSignatureBlock::class,
    'table' => OcrTableBlock::class,
    'text' => OcrTextBlock::class,
    'title' => OcrTitleBlock::class,
])]
abstract readonly class OcrBlock
{
}
