<?php declare(strict_types=1);
namespace LightApp\Model\Middleware;

use LightApp\Model\Middleware\MiddlewareAbstract;
use LightApp\Controller\ControllerInterface;
use LightApp\Model\System\Request;
use LightApp\Model\System\Response;

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
