<?php declare(strict_types=1);
namespace LightApp\Model\Middleware;

use LightApp\Model\System\Request;
use LightApp\Model\System\Response;

interface MiddlewareInterface
{
    public function process(Request $request) : Response;
}
