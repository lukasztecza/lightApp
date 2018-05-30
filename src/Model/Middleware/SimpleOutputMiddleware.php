<?php declare(strict_types=1);
namespace LightApp\Model\Middleware;

use LightApp\Model\System\Request;
use LightApp\Model\System\Response;
use LightApp\Model\Middleware\MiddlewareAbstract;
use LightApp\Model\Middleware\MiddlewareInterface;

class SimpleOutputMiddleware extends MiddlewareAbstract
{
    private const CONTENT_TYPE_HTML = 'text/html';
    private const CONTENT_TYPE_JSON = 'application/json';

    private const TEMPLATES_PATH = APP_ROOT_DIR . '/src/View';

    private $defaultContentType;

    public function __construct(
        MiddlewareInterface $next,
        string $defaultContentType
    ) {
        parent::__construct($next);
        $this->defaultContentType = $defaultContentType;
    }

    public function process(Request $request) : Response
    {
        $response = $this->getNext()->process($request);
        $headers = $response->getHeaders();
        $headers['Content-Type'] = $headers['Content-Type'] ?? $this->defaultContentType;
        $location = $headers['Location'] ?? null;
        $contentType = $headers['Content-Type'] ?? null;

        switch (true) {
            case $location:
                $this->setHeaders($headers);
                break;
            case $contentType === self::CONTENT_TYPE_HTML:
                $this->buildHtmlResponse($response->getFile(), $response->getVariables(), $headers, $response->getCookies());
                break;
            case $contentType === self::CONTENT_TYPE_JSON:
                $this->buildJsonResponse($response->getVariables(), $headers);
                break;
            default:
                throw new \Exception('Not supported Content-Type ' . $contentType);
        }

        return $response;
    }

    protected function setHeaders(array $headers) : void
    {
        foreach ($headers as $key => $value) {
            if (!is_numeric($key)) {
                header($key . ': ' . $value);
            } else {
                header($value);
            }
        }
    }

    protected function setCookies(array $cookies) : void
    {
        foreach ($cookies as $cookie) {
            setcookie(
                $cookie['name'],
                $cookie['value'],
                $cookie['expire'],
                $cookie['path'],
                $cookie['domain'],
                $cookie['secure'],
                $cookie['httponly']
            );
        }
    }

    protected function buildJsonResponse(array $variables, array $headers) : void
    {
        $this->setHeaders($headers);
        echo json_encode($variables);
    }

    protected function buildHtmlResponse(string $template, array $variables, array $headers, array $cookies) : void
    {
        if (empty($template) || !file_exists(self::TEMPLATES_PATH . '/' . $template)) {
            throw new \Exception('Template does not exist ' . var_export($template, true));
        }

        $headers['Content-Security-Policy'] = "default-src 'none'; script-src 'self'; style-src 'self'; img-src 'self'";
        $this->setHeaders($headers);
        $this->setCookies($cookies);

        extract($variables);
        unset($variables);
        unset($headers);
        unset($cookies);

        include(self::TEMPLATES_PATH . '/' . $template);
    }
}
