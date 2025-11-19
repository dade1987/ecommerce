<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use function Safe\gzencode;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CompressResponse
{
    private const MIN_LENGTH_BYTES = 1024;

    /**
     * Content types eligible for gzip compression.
     * Keep this conservative to avoid corrupting binary outputs or streams.
     */
    private const COMPRESSIBLE_MIME_TYPES = [
        'text/plain',
        'text/html',
        'text/css',
        'text/xml',
        'text/javascript',
        'application/javascript',
        'application/x-javascript',
        'application/json',
        'application/xml',
        'application/rss+xml',
        'application/atom+xml',
        'application/xhtml+xml',
        'image/svg+xml',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if ($this->shouldBypassCompression($request, $response)) {
            return $response;
        }

        $acceptEncoding = (string) $request->headers->get('Accept-Encoding', '');
        if (stripos($acceptEncoding, 'gzip') === false) {
            return $response; // client does not accept gzip
        }

        $content = $response->getContent();
        if ($content === '' || $content === null) {
            return $response;
        }

        if (! function_exists('gzencode')) {
            return $response; // zlib missing
        }

        $compressed = gzencode($content, 6);
        if ($compressed === false) {
            return $response; // compression failed, leave as-is
        }

        $response->setContent($compressed);
        // Headers update
        $response->headers->set('Content-Encoding', 'gzip', true);
        $this->appendVary($response, 'Accept-Encoding');
        // Remove ETag to avoid mismatch and let intermediaries re-calc if needed
        $response->headers->remove('ETag');
        // Let PHP/Server decide chunking; content-length can be omitted safely
        $response->headers->remove('Content-Length');

        return $response;
    }

    private function shouldBypassCompression(Request $request, Response $response): bool
    {
        // Do not compress streamed or binary file responses
        if ($response instanceof StreamedResponse || $response instanceof BinaryFileResponse) {
            return true;
        }

        // Already encoded by an upstream or previous layer
        if ($response->headers->has('Content-Encoding')) {
            return true;
        }

        // Skip informational, 204 and 304 responses
        $status = $response->getStatusCode();
        if ($status < 200 || $status === 204 || $status === 304) {
            return true;
        }

        $contentTypeHeader = (string) $response->headers->get('Content-Type', '');
        if ($contentTypeHeader === '') {
            return true;
        }

        // Server-Sent Events must not be compressed
        if (stripos($contentTypeHeader, 'text/event-stream') !== false) {
            return true;
        }

        $mimeType = strtolower(trim(strtok($contentTypeHeader, ';')));
        if (! in_array($mimeType, self::COMPRESSIBLE_MIME_TYPES, true)) {
            return true;
        }

        $content = $response->getContent();
        $length = is_string($content) ? strlen($content) : 0;
        if ($length < self::MIN_LENGTH_BYTES) {
            return true;
        }

        return false;
    }

    private function appendVary(Response $response, string $headerName): void
    {
        $existing = $response->headers->get('Vary');
        if ($existing === null || $existing === '') {
            $response->headers->set('Vary', $headerName);

            return;
        }

        $parts = array_filter(array_map('trim', explode(',', $existing)));
        if (! in_array($headerName, $parts, true)) {
            $parts[] = $headerName;
            $response->headers->set('Vary', implode(', ', $parts));
        }
    }
}
