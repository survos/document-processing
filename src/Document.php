<?php

declare(strict_types=1);

namespace Survos\DocumentProcessing;

final readonly class Document
{
    private function __construct(private array $wire, public ?FileReference $reference = null)
    {
    }

    public static function image(string $url): self
    {
        return new self(['type' => 'image_url', 'image_url' => $url]);
    }

    public static function pdf(string $url): self
    {
        return new self(['type' => 'document_url', 'document_url' => $url]);
    }

    public static function file(FileReference $file): self
    {
        return new self(['type' => 'file', 'file_id' => $file->id], $file);
    }

    public function toArray(): array
    {
        return $this->wire;
    }
}
