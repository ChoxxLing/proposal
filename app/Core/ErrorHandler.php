<?php

class ErrorHandler
{
    private static array $config = [
        'debug' => false,
        'log_file' => __DIR__ . '/../../storage/logs/app.log',
    ];

    public static function register(array $config): void
    {
        self::$config = array_merge(self::$config, $config);

        ini_set('display_errors', self::debug() ? '1' : '0');
        error_reporting(E_ALL);

        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    public static function debug(): bool
    {
        return (bool) self::$config['debug'];
    }

    public static function handleError(int $severity, string $message, string $file, int $line): bool
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        throw new ErrorException($message, 0, $severity, $file, $line);
    }

    public static function handleException(Throwable $exception): void
    {
        self::log($exception);

        if (!headers_sent()) {
            Response::json(self::payload($exception), 500);
        }

        echo self::debug()
            ? '<pre>' . htmlspecialchars((string) $exception) . '</pre>'
            : 'An application error occurred.';
        exit;
    }

    public static function handleShutdown(): void
    {
        $error = error_get_last();
        if (!$error || !in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            return;
        }

        $exception = new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
        self::log($exception);

        if (!headers_sent()) {
            Response::json(self::payload($exception), 500);
        }
    }

    public static function log(Throwable $exception): void
    {
        $file = self::$config['log_file'];
        $dir = dirname($file);

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $message = sprintf(
            "[%s] %s: %s in %s:%d\n%s\n\n",
            date('Y-m-d H:i:s'),
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );

        file_put_contents($file, $message, FILE_APPEND);
    }

    public static function payload(Throwable $exception): array
    {
        $payload = ['error' => 'Server error. Check storage/logs/app.log for details.'];

        if (self::debug()) {
            $payload['debug'] = [
                'type' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ];
        }

        return $payload;
    }
}
