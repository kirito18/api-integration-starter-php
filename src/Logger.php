<?php
declare(strict_types=1);

namespace App;

final class Logger
{
    public function __construct(
        private string $logPath
    ) {}

    public function info(string $message, array $context = []): void
    {
        $this->write('INFO', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->write('ERROR', $message, $context);
    }

    private function write(string $level, string $message, array $context): void
    {
        $dir = dirname($this->logPath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $entry = [
            'ts' => gmdate('c'),
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ];

        file_put_contents(
            $this->logPath,
            json_encode($entry, JSON_UNESCAPED_SLASHES) . PHP_EOL,
            FILE_APPEND
        );
    }
}