<?php declare(strict_types=1);
namespace LightApp\Model\System;

class ErrorHandler
{
    protected const CONTENT_TYPE_JSON = 'application/json';
    protected const CONTENT_TYPE_HTML = 'text/html';
    protected const LOGS_PATH = APP_ROOT_DIR . '/tmp/logs';
    protected const PRODUCTION_ENVIRONMENT = 'prod';

    private $defaultContentType;

    public function __construct(string $environment, string $defaultContentType = null)
    {
        $this->defaultContentType = $defaultContentType;

        if (static::PRODUCTION_ENVIRONMENT === $environment) {
            error_reporting(E_ALL & ~E_USER_NOTICE);
            set_error_handler([$this, 'handleError']);
            set_exception_handler([$this, 'handleException']);
            register_shutdown_function([$this, 'handleShutDown']);
            return;
        }

        error_reporting(E_ALL);
    }

    public function handleShutDown() : void
    {
        $error = error_get_last();
        if ($error) {
            $this->log($error["type"], $error["message"], $error["file"], $error["line"], 'Shutdown Error');
        }
    }

    public function handleError(int $type = null, string $message = null, string $file = null, int $line = null, array $context = []) : void
    {
        if (!(error_reporting() & $type)) {
            $this->log($type, $message, $file, $line, 'Info');
            return;
        }

        $this->log($type, $message, $file, $line, 'Error');
        $this->displayErorPage($type);
    }

    public function handleException(\Throwable $exception) : void
    {
        $this->log(
            $exception->getCode(),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            get_class($exception)
        );
        $this->displayErorPage($exception->getCode());
    }

    protected function log(int $type, string $message, string $file, int $line, string $reason) : void
    {
        $message = json_encode($message);
        $message = preg_replace(['/[^a-zA-Z0-9 ]/', '/_{1,}/'], '_', $message);

        if (!file_exists(static::LOGS_PATH)) {
            mkdir(static::LOGS_PATH, 0775, true);
        }
        file_put_contents(
            static::LOGS_PATH . '/' . 'php-' . date('Y-m-d') . '.log',
            date('Y-m-d H:i:s') . ' | ' . $reason .  ' | code: ' . $type . ' | file: ' . $file . ' | line: ' . $line .
            ' | with message: ' . $message . PHP_EOL . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }

    protected function displayErorPage(int $code = null) : void
    {
        switch ($this->defaultContentType) {
            case static::CONTENT_TYPE_JSON:
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'code' => $code]);
                break;
            case static::CONTENT_TYPE_HTML:
                echo
                    '<!Doctype html>' .
                    '<html>' .
                    '<head><meta charset="utf-8"><meta name="robots" content="noindex, nofollow"></head>' .
                    '<body><p>Status: error</p><p>Code: ' . $code . '</p><p><a href="/">Go to home page</a></p></body>' .
                    '</html>'
                ;
                break;
            default:
                echo 'Could not finish because of error code ' . $code . ', see logs for details' . PHP_EOL;
        }
        exit;
    }
}
