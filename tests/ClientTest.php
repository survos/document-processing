<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Survos\DocumentProcessing\ApiException;
use Survos\DocumentProcessing\Client;
use Survos\DocumentProcessing\Document;
use Survos\DocumentProcessing\Dto\FileSchema;
use Survos\DocumentProcessing\Dto\OcrImageBlock;
use Survos\DocumentProcessing\Dto\OcrPageObject;
use Survos\DocumentProcessing\Dto\OcrTextBlock;
use Survos\DocumentProcessing\OcrRequest;
use Survos\DocumentProcessing\SerializerFactory;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class ClientTest extends TestCase
{
    private function fixture(): array
    {
        return ['model' => 'rusto-tiny', 'pages' => [[
            'index' => 0, 'markdown' => 'Hello', 'images' => [], 'dimensions' => ['dpi' => 150, 'width' => 500, 'height' => 700],
            'blocks' => [['type' => 'text', 'content' => 'Hello', 'top_left_x' => 1, 'top_left_y' => 2, 'bottom_right_x' => 40, 'bottom_right_y' => 20]],
        ]], 'usage_info' => ['pages_processed' => 1], 'extension' => 'retained'];
    }

    private function client(MockHttpClient $http, string $key = ''): Client
    {
        return new Client($http, SerializerFactory::create(), 'local', 'http://localhost:9000/service/v1/', $key);
    }

    public function testNestedResponseAndWireRequest(): void
    {
        $http = new MockHttpClient(function ($method, $url, $options) {
            self::assertSame('POST', $method);
            self::assertSame('http://localhost:9000/service/v1/ocr', $url);
            $body = json_decode($options['body'], true);
            self::assertSame([0], $body['pages']);
            self::assertSame('image_url', $body['document']['type']);
            self::assertSame(0, $options['max_redirects']);

            return new MockResponse(json_encode($this->fixture()));
        });
        $result = $this->client($http)->ocr->process(new OcrRequest(Document::image('https://example.org/a.jpg'), 'rusto-tiny', [0]));
        self::assertInstanceOf(OcrPageObject::class, $result->data->pages[0]);
        self::assertInstanceOf(OcrTextBlock::class, $result->data->pages[0]->blocks[0]);
        self::assertSame(40, $result->data->pages[0]->blocks[0]->bottomRightX);
        self::assertSame(1, $result->data->usageInfo->pagesProcessed);
        self::assertSame('retained', $result->raw['extension']);
    }

    public function testImageDiscriminatorAndConfidence(): void
    {
        $data = $this->fixture();
        $block = &$data['pages'][0]['blocks'][0];
        $block['type'] = 'image';
        $block['image_id'] = 'figure-1';
        $block['confidence_scores'] = ['average_content_confidence_score' => 0.9];
        $result = $this->client(new MockHttpClient(new MockResponse(json_encode($data))))->ocr->process(new OcrRequest(Document::image('https://example.org/a.jpg'), 'tiny'));
        self::assertInstanceOf(OcrImageBlock::class, $result->data->pages[0]->blocks[0]);
        self::assertSame('figure-1', $result->data->pages[0]->blocks[0]->imageId);
        self::assertSame(0.9, $result->data->pages[0]->blocks[0]->confidenceScores->averageContentConfidenceScore);
    }

    public function testMissingRequiredFieldFails(): void
    {
        $data = $this->fixture();
        unset($data['pages'][0]['dimensions']);
        $this->expectException(Symfony\Component\Serializer\Exception\MissingConstructorArgumentsException::class);
        $this->client(new MockHttpClient(new MockResponse(json_encode($data))))->ocr->process(new OcrRequest(Document::pdf('https://example.org/a.pdf'), 'rusto-tiny'));
    }

    public function testExplicitNullDimensionsAndUnknownDiscriminator(): void
    {
        $data = $this->fixture();
        $data['pages'][0]['dimensions'] = null;
        $client = $this->client(new MockHttpClient(new MockResponse(json_encode($data))));
        self::assertNull($client->ocr->process(new OcrRequest(Document::image('https://example.org/a.jpg'), 'tiny'))->data->pages[0]->dimensions);
        $data['pages'][0]['blocks'][0]['type'] = 'future_block';
        $this->expectException(Symfony\Component\Serializer\Exception\NotNormalizableValueException::class);
        $this->client(new MockHttpClient(new MockResponse(json_encode($data))))->ocr->process(new OcrRequest(Document::image('https://example.org/a.jpg'), 'tiny'));
    }

    private function file(string $id): array
    {
        return ['id' => $id, 'object' => 'file', 'bytes' => 10, 'created_at' => 1, 'filename' => 'x.pdf', 'purpose' => 'ocr', 'sample_type' => 'future', 'source' => 'upload'];
    }

    public function testPaginationBeyondFirstPage(): void
    {
        $http = new MockHttpClient(function ($method, $url) {
            parse_str(parse_url($url, PHP_URL_QUERY), $query);
            $page = (int) $query['page'];

            return new MockResponse(json_encode(['data' => [$this->file((string) $page)], 'object' => 'list', 'total' => 2]));
        });
        $items = iterator_to_array($this->client($http)->files->iterate(1));
        self::assertCount(2, $items);
        self::assertInstanceOf(FileSchema::class, $items[1]);
        self::assertSame('1', $items[1]->id);
    }

    public function testErrorsAreTypedAndDoNotLeakProviderBodyInMessage(): void
    {
        $client = $this->client(new MockHttpClient(new MockResponse('{"message":"private-url"}', ['http_code' => 422, 'response_headers' => ['x-request-id: abc']])));
        try {
            $client->files->retrieve($client->files->reference('x'));
            self::fail('Expected error');
        } catch (ApiException $e) {
            self::assertSame(422, $e->status);
            self::assertSame('abc', $e->requestId);
            self::assertStringNotContainsString('private-url', $e->getMessage());
        }
    }

    public function testFileCannotCrossAccounts(): void
    {
        $http = new MockHttpClient();
        $one = $this->client($http, 'one');
        $two = $this->client($http, 'two');
        $this->expectException(InvalidArgumentException::class);
        $two->ocr->process(new OcrRequest(Document::file($one->files->reference('id')), 'tiny'));
    }

    public function testUploadDownloadAndFileOperations(): void
    {
        $calls = 0;
        $http = new MockHttpClient(function ($method, $url, $options) use (&$calls) {
            ++$calls;
            if ('POST' === $method) {
                self::assertIsCallable($options['body']);
                $body = '';
                while (($chunk = $options['body'](8192)) !== '') {
                    $body .= $chunk;
                }
                self::assertStringContainsString('purpose', $body);
                self::assertStringContainsString('payload', $body);

                return new MockResponse(json_encode($this->file('new')));
            }
            if ('DELETE' === $method) {
                return new MockResponse('{"id":"new","object":"file","deleted":true}');
            }
            if (str_contains($url, '/url?')) {
                return new MockResponse('{"url":"https://example.org/signed"}');
            }

            return new MockResponse('payload');
        });
        $client = $this->client($http);
        $path = tempnam(sys_get_temp_dir(), 'document-test');
        file_put_contents($path, 'payload');
        try {
            $file = $client->files->upload($path)->data;
            $ref = $client->files->reference($file->id);
            self::assertSame('https://example.org/signed', $client->files->signedUrl($ref)->data->url);
            self::assertSame('payload', implode('', iterator_to_array($client->files->download($ref))));
            self::assertTrue($client->files->delete($ref)->data->deleted);
            self::assertSame(4, $calls);
        } finally {
            unlink($path);
        }
    }
}
