<?php

declare(strict_types=1);

namespace Survos\DocumentProcessing;

use Survos\DocumentProcessing\Dto\OcrResponse;

/** Shared boundary for HTTP clients and local processing adapters. */
interface OcrProcessorInterface
{
    /** @return Result<OcrResponse> */
    public function process(OcrRequest $request): Result;
}
