<?php
declare(strict_types=1);

namespace App\Http;

use App\Logger;

final class HttpClient
{
    public function __construct(
        private string $baseUrl,
        private int $timeoutSeconds,
        private ?Logger $logger = null,
        private array $defaultHeaders = []
    ) {}

    /**
     * @param array<string,mixed> $json
     * @return array{status:int, headers:array<string,string>, body:string, json:array<string,mixed>|null}
     */
    public function post(string $path, array $json, array $headers = []): array
    {
        return $this->request('POST', $path, $json, $headers);
    }

    /**
     * @return array{status:int, headers:array<string,string>, body:string, json:array<string,mixed>|null}
     */
    public function get(string $path, array $headers = []): array
    {
        return $this->request('GET', $path, null, $headers);
    }

    /**
     * @param array<string,mixed>|null $json
     * @return array{status:int, headers:array<string,string>, body:string, json:array<string,mixed>|null}
     */
    private function request(string $method, string $path, ?array $json, array $headers = []): array
    {
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($path, '/');

        $ch = curl_init($url);
        if ($ch === false) {
            throw new \RuntimeException('Unable to init cURL');
        }

        $mergedHeaders = array_merge($this->defaultHeaders, $headers);

        $headerLines = [];
        foreach ($mergedHeaders as $k => $v) {
            $headerLines[] = $k . ': ' . $v;
        }

        $payload = null;
        if ($json !== null) {
            $payload = json_encode($json, JSON_UNESCAPED_SLASHES);
            $headerLines[] = 'Content-Type: application/json';
        }

        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_TIMEOUT => $this->timeoutSeconds,
            CURLOPT_HTTPHEADER => $headerLines,
        ]);

        if ($payload !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        }

        $this->logger?->info('HTTP request', [
            'method' => $method,
            'url' => $url,
            'has_body' => $payload !== null,
        ]);

        $raw = curl_exec($ch);

        if ($raw === false) {
            $err = curl_error($ch);
            $code = curl_errno($ch);
            curl_close($ch);

            $this->logger?->error('HTTP error (cURL)', [
                'errno' => $code,
                'error' => $err,
                'url' => $url,
            ]);

            throw new \RuntimeException('cURL error: ' . $err, $code);
        }

        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = (int)curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        $headerRaw = substr($raw, 0, $headerSize);
        $body = (string)substr($raw, $headerSize);

        $parsedHeaders = $this->parseHeaders($headerRaw);

        $jsonBody = null;
        $trim = ltrim($body);
        if ($trim !== '' && (str_starts_with($trim, '{') || str_starts_with($trim, '['))) {
            $decoded = json_decode($body, true);
            if (is_array($decoded)) {
                $jsonBody = $decoded;
            }
        }

        $this->logger?->info('HTTP response', [
            'status' => $status,
            'url' => $url,
        ]);

        return [
            'status' => $status,
            'headers' => $parsedHeaders,
            'body' => $body,
            'json' => $jsonBody,
        ];
    }

    /**
     * @return array<string,string>
     */
    private function parseHeaders(string $rawHeaders): array
    {
        $headers = [];
        $lines = preg_split("/\r\n|\n|\r/", trim($rawHeaders)) ?: [];
        foreach ($lines as $line) {
            if (str_contains($line, ':')) {
                [$k, $v] = explode(':', $line, 2);
                $headers[trim($k)] = trim($v);
            }
        }
        return $headers;
    }
}