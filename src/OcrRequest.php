<?php

declare(strict_types=1);

namespace Survos\DocumentProcessing;

final readonly class OcrRequest
{
    /** @param list<int>|null $pages Zero-based page indexes. Null means omit. */
    public function __construct(public Document $document, public string $model, public ?array $pages = null,
        public bool $includeBlocks = true, public bool $includeImageBase64 = false)
    {
        if ('' === trim($model)) {
            throw new \InvalidArgumentException('Select a model explicitly.');
        }
        foreach ($pages ?? [] as $page) {
            if (!is_int($page) || $page < 0) {
                throw new \InvalidArgumentException('Page indexes must be nonnegative integers.');
            }
        }
    }

    public function toArray(): array
    {
        $data = ['document' => $this->document->toArray(), 'model' => $this->model,
            'include_blocks' => $this->includeBlocks, 'include_image_base64' => $this->includeImageBase64];
        if (null !== $this->pages) {
            $data['pages'] = $this->pages;
        }

        return $data;
    }
}
