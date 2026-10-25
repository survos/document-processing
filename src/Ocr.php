<?php

declare(strict_types=1);

namespace Survos\DocumentProcessing;

use Survos\DocumentProcessing\Dto\OcrResponse;

final readonly class Ocr implements OcrProcessorInterface
{
    public function __construct(private Transport $transport)
    {
    }

    /** @return Result<OcrResponse> */
    public function process(OcrRequest $request): Result
    {
        if (null !== $request->document->reference && $request->document->reference->scope !== $this->transport->scope) {
            throw new \InvalidArgumentException('File belongs to a different endpoint/account.');
        }

        return $this->transport->json('POST', 'ocr', OcrResponse::class, ['json' => $request->toArray()]);
    }
}
