# Survos Document Processing

A standalone PHP 8.5 / Symfony 8.1 client for a documented subset of the Mistral Files and OCR wire contract. It can target Mediary once its compatible server endpoints exist. This library does not implement those endpoints or run an OCR engine.

```php
use Survos\DocumentProcessing\{Client, Document, OcrRequest, SerializerFactory};
use Symfony\Component\HttpClient\HttpClient;

$client = new Client(HttpClient::create(), SerializerFactory::create(),
    name: 'mediary', baseUri: 'https://your-mediary.example/v1', apiKey: $key);
$result = $client->ocr->process(new OcrRequest(
    Document::image('https://your-storage.example/scan.jpg'),
    model: 'rusto-tiny', pages: [0],
));
foreach ($result->data->pages as $page) {
    echo $page->markdown;
    foreach ($page->blocks ?? [] as $block) {
        // Typed discriminated block objects; page-image pixel coordinates.
    }
}
// $result->raw retains unknown fields, explicit nulls and original evidence.
// $result->requestId is available when the server supplies x-request-id.
```

There is no implicit endpoint, model, fallback to paid OCR, or environment access. A Mistral client must explicitly select its base URI, credentials and model. API compatibility does not imply equal OCR or layout quality: Tiny text-line boxes must not be advertised as paragraph segmentation.

## Files

```php
$file = $client->files->upload('/archive/scan.pdf')->data; // multipart, purpose=ocr
$reference = $client->files->reference($file->id);
$result = $client->ocr->process(new OcrRequest(Document::file($reference), 'rusto-tiny'));
foreach ($client->files->iterate(pageSize: 100) as $file) { /* typed metadata */ }
$metadata = $client->files->retrieve($reference);
$url = $client->files->signedUrl($reference, expiryHours: 1)->data->url;
foreach ($client->files->download($reference) as $bytes) { /* write to owned destination */ }
$deleted = $client->files->delete($reference)->data->deleted;
```

Files are scoped to the client name, full base URI and credentials. Persist client identity alongside remote IDs; reconstruct references using the matching client's `files->reference()`. A signed URL is temporary access, not archive identity. Upload paths remain caller-owned. Downloads stream with buffering disabled; consume or destroy the generator to cancel a partially read response. Multipart uses Symfony Mime's iterable body; no whole-file concatenation. Tests verify this transport shape, not a measured large-file memory ceiling.

## Supported surface and limits

- Files: upload for OCR, one-page list and iterator, retrieve, delete, signed URL, streamed download.
- OCR: image URL, document URL (PDF), file reference, explicit model, zero-based integer page list, block and embedded-image toggles.
- Typed response pages, dimensions, all currently documented block discriminators, images, tables, confidence and usage. Unknown block types fail explicitly; unknown file metadata strings and raw fields survive.
- Optional response properties are nullable. Consult `raw` to distinguish omitted from explicit null. Required nullable properties still must be supplied.
- Advanced annotation requests, table-format controls, confidence requests, batches, chat and S3 registration extensions are not exposed in this version. Office formats are not verified.
- HTTP errors preserve status, request ID and raw body in `ApiException`; messages omit provider bodies to avoid accidental URL disclosure. Transport/network errors retain Symfony's exception types. Do not log credentials or raw signed-URL responses.
- No automatic retries or caching are added. Inject a non-retrying HTTP client for paid processing; application-wide decorators may otherwise retry. Durable jobs, polling and processing-result caching belong to the application. Cache results by content revision, options and engine version; mutable model aliases and URLs are insufficient.
- Redirects are disabled to prevent credential forwarding. Base URI includes `/v1` (or an alternative prefix) exactly once.
- Existing `survos/mistral-bundle` callers remain unchanged; this is a separate client, not a replacement release of that bundle.

See [SCHEMA.md](SCHEMA.md) for the pinned public specification and digest. No live or paid requests were used for validation.

Run from the mono root:

```sh
vendor/bin/phpunit --no-configuration --bootstrap lib/document-processing/tests/bootstrap.php lib/document-processing/tests bu/document-processing-bundle/tests
```
