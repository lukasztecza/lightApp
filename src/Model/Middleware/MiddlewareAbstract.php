<?php declare(strict_types=1);
namespace LightApp\Model\Middleware;

use LightApp\Model\Middleware\MiddlewareInterface;
use LightApp\Model\System\Request;
use LightApp\Model\System\Response;

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
