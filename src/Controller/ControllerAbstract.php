<?php declare(strict_types=1);
namespace LightApp\Controller;

use LightApp\Controller\ControllerInterface;
use LightApp\Model\System\Request;
use LightApp\Model\System\Response;

abstract class ControllerAbstract implements ControllerInterface
{
    protected function jsonResponse(array $variables, array $escapeRules = []) : Response
    {
        return new Response(null, $variables, $escapeRules, ['Content-Type' => 'application/json']);
    }

    protected function htmlResponse(string $file, array $variables = [], array $escapeRules = []) : Response
    {
        return new Response($file, $variables, $escapeRules, ['Content-Type' => 'text/html']);
    }

    protected function redirectResponse(string $location) : Response
    {
        return new Response(null, [], [], ['Location' => $location]);
    }

    protected function codeResponse(Request $request, int $code, string $contentType = null) : Response
    {
        $message = '';
        switch ($code) {
            case 200:
                $message .= ' Ok';
                break;
            case 201:
                $message .= ' Created';
                break;
            case 204:
                $message .= ' No Content';
                break;
            case 207:
                $message .= ' Multi Status';
                break;
            case 301:
                $message .= ' Moved Permanently';
                break;
            case 302:
                $message .= ' Found';
                break;
            case 400:
                $message .= ' Bad Request';
                break;
            case 401:
                $message .= ' Unauthorized';
                break;
            case 403:
                $message .= ' Forbidden';
                break;
            case 404:
                $message .= ' Not Found';
                break;
            case 409:
                $message .= ' Conflict';
                break;
            default:
                $code = 500;
                $message .= ' Internal Server Error';
                break;
        }
        $headers = [$request->getServerProtocol() . ' ' . $code . $message];
        if ($contentType) {
            $headers['Content-Type'] = $contentType;
        }

        return new Response(
            ($contentType === 'text/html' ? 'errorCode.php' : null),
            [],
            [],
            $headers
        );
    }
}
