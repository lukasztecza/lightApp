<?php
namespace TinyAppBase\Model\Middleware;

use TinyAppBase\Model\Middleware\MiddlewareInterface;
use TinyAppBase\Model\System\Request;
use TinyAppBase\Model\System\Response;

abstract class MiddlewareAbstract implements MiddlewareInterface
{
    private $next;

    public function __construct(MiddlewareInterface $next)
    {
        $this->next = $next;
    }

    protected function getNext() : MiddlewareInterface
    {
        return $this->next;
    }

    abstract public function process(Request $request) : Response;
}
