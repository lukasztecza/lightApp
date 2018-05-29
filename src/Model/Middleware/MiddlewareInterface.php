<?php declare(strict_types=1);
namespace TinyAppBase\Model\Middleware;

use TinyAppBase\Model\System\Request;
use TinyAppBase\Model\System\Response;

interface MiddlewareInterface
{
    public function process(Request $request) : Response;
}
