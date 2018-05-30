<?php declare(strict_types=1);
namespace LightApp\Controller;

use LightApp\Model\System\Request;
use LightApp\Model\System\Response;

abstract class ControllerAbstract
{
    protected function jsonResponse(array $variables, array $escapeRules = []) : Response
    {
        return new Response(null, $variables, $escapeRules, ['Content-Type' => 'application/json']);

    }

    protected function htmlResponse(string $file, array $variables = [], array $escapeRules = []) : Response
    {
        return new Response($file, $variables, $escapeRules, ['Content-Type' => 'text/html']);
    }

    protected function codeResponse(int $code, Request $request, array $variables = [], array $escapeRules = []) : Response
    {
        $message = ' ';
        switch ($code) {
            case 404:
                $message .= 'Not found';
                break;
        }
        return new Response('errorCode.php', [], [], [$request->getServerProtocol() . ' ' . $code . $message]);
    }
}
