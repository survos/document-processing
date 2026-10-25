<?php

declare(strict_types=1);

namespace Survos\DocumentProcessing;

use Survos\DocumentProcessing\Dto\DeleteFileResponse;
use Survos\DocumentProcessing\Dto\FileSchema;
use Survos\DocumentProcessing\Dto\ListFilesResponse;
use Survos\DocumentProcessing\Dto\SignedUrl;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;

final readonly class Files
{
    public function __construct(private Transport $transport)
    {
    }

    public function reference(string $id): FileReference
    {
        return new FileReference($this->transport->scope, $id);
    }

    /** @return Result<FileSchema> */
    public function retrieve(FileReference $file): Result
    {
        return $this->transport->json('GET', $this->path($file), FileSchema::class);
    }

    /** @return Result<ListFilesResponse> One page, numbered from zero. */
    public function list(int $page = 0, int $pageSize = 100, ?string $search = null): Result
    {
        if ($page < 0 || $pageSize < 1) {
            throw new \InvalidArgumentException('Invalid pagination.');
        }
        $query = ['page' => $page, 'page_size' => $pageSize, 'include_total' => true];
        if (null !== $search) {
            $query['search'] = $search;
        }

        return $this->transport->json('GET', 'files', ListFilesResponse::class, ['query' => $query]);
    }

    /** @return \Generator<FileSchema> */
    public function iterate(int $pageSize = 100, ?string $search = null): \Generator
    {
        $seen = 0;
        for ($page = 0;; ++$page) {
            $result = $this->list($page, $pageSize, $search)->data;
            foreach ($result->data as $file) {
                ++$seen;
                yield $file;
            }
            if (count($result->data) < $pageSize || (null !== $result->total && $seen >= $result->total)) {
                return;
            }
        }
    }

    /** @return Result<FileSchema> The local file remains caller-owned. Multipart bytes are streamed. */
    public function upload(string $path, ?string $filename = null): Result
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new \InvalidArgumentException('A readable local file is required.');
        }
        $form = new FormDataPart(['purpose' => 'ocr', 'file' => DataPart::fromPath($path, $filename)]);

        return $this->transport->json('POST', 'files', FileSchema::class, [
            'headers' => $form->getPreparedHeaders()->toArray(), 'body' => $form->bodyToIterable(),
        ]);
    }

    /** @return Result<DeleteFileResponse> */
    public function delete(FileReference $file): Result
    {
        return $this->transport->json('DELETE', $this->path($file), DeleteFileResponse::class);
    }

    /** @return Result<SignedUrl> */
    public function signedUrl(FileReference $file, int $expiryHours = 1): Result
    {
        if ($expiryHours < 1) {
            throw new \InvalidArgumentException('Expiry must be positive.');
        }

        return $this->transport->json('GET', $this->path($file).'/url', SignedUrl::class, ['query' => ['expiry' => $expiryHours]]);
    }

    /** @return \Generator<string> */
    public function download(FileReference $file): \Generator
    {
        return $this->transport->download($this->path($file).'/content');
    }

    private function path(FileReference $file): string
    {
        if ($file->scope !== $this->transport->scope) {
            throw new \InvalidArgumentException('File belongs to a different endpoint/account.');
        }

        return 'files/'.rawurlencode($file->id);
    }
}
