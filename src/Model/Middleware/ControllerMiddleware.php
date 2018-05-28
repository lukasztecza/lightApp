<?php
namespace TinyAppBase\Model\Middleware;

use TinyAppBase\Controller\ControllerInterface;
use TinyAppBase\Model\System\Request;
use TinyAppBase\Model\System\Response;
use TinyAppBase\Model\Middleware\MiddlewareAbstract;

class ControllerMiddleware extends MiddlewareAbstract
{
    private $routedController;
    private $routedAction;

    public function __construct(ControllerInterface $controller, string $action)
    {
        $this->controller = $controller;
        $this->action = $action;
    }

    public function process(Request $request) : Response
    {
        $controller = $this->controller;
        $action = $this->action;
        return $controller->$action($request);
    }
}
