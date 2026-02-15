<?php
declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) return;

    $relative = substr($class, strlen($prefix));
    $path = __DIR__ . '/../src/' . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($path)) require $path;
});

use App\Config;
use App\Logger;
use App\Http\HttpClient;

Config::loadEnv(__DIR__ . '/../.env');

$logger = new Logger(Config::get('LOG_PATH', __DIR__ . '/../storage/logs/app.log'));

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

header('Content-Type: application/json');

try {
    // Healthcheck endpoint
    if ($method === 'GET' && $uri === '/health') {
        echo json_encode([
            'ok' => true,
            'service' => 'api-integration-starter-php',
            'ts' => gmdate('c'),
        ], JSON_UNESCAPED_SLASHES);
        exit;
    }

    // Demo endpoint: POST /proxy (forwards a request to an external API)
    if ($method === 'POST' && $uri === '/proxy') {
        $raw = file_get_contents('php://input') ?: '';
        $payload = json_decode($raw, true);
        if (!is_array($payload)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON body'], JSON_UNESCAPED_SLASHES);
            exit;
        }

        $client = new HttpClient(
            baseUrl: Config::get('API_BASE_URL', 'https://httpbin.org'),
            timeoutSeconds: Config::int('TIMEOUT', 30),
            logger: $logger,
            defaultHeaders: [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . Config::get('API_KEY', 'demo_key'),
            ]
        );

        // forwards to /post by default
        $resp = $client->post('/post', $payload);

        http_response_code($resp['status']);
        echo json_encode([
            'forwarded_to' => Config::get('API_BASE_URL', 'https://httpbin.org') . '/post',
            'status' => $resp['status'],
            'response_json' => $resp['json'],
        ], JSON_UNESCAPED_SLASHES);
        exit;
    }

    http_response_code(404);
    echo json_encode(['error' => 'Not Found', 'path' => $uri], JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    $logger->error('Unhandled exception', [
        'message' => $e->getMessage(),
        'code' => $e->getCode(),
    ]);

    http_response_code(500);
    echo json_encode(['error' => 'Server Error'], JSON_UNESCAPED_SLASHES);
}