<?php declare(strict_types=1);
namespace TinyAppBase\Model\Middleware;

use TinyAppBase\Model\Service\SessionService;
use TinyAppBase\Controller\ControllerInterface;
use TinyAppBase\Model\System\Request;
use TinyAppBase\Model\System\Response;
use TinyAppBase\Model\Middleware\MiddlewareAbstract;
use TinyAppBase\Model\Middleware\MiddlewareInterface;

class SecurityMiddleware extends MiddlewareAbstract
{
    private const LOGIN_ROUTE = '/login';

    private $securityList;
    private $sessionService;

    public function __construct(MiddlewareInterface $next, array $securityList, SessionService $sessionService)
    {
        parent::__construct($next);
        $this->securityList = $securityList;
        $this->sessionService = $sessionService;
    }

    public function process(Request $request) : Response
    {
        $roles = $this->sessionService->get(['roles'])['roles'];

        $included = $permitted = false;
        foreach ($this->securityList as $ruleKey => $rule) {
            if (!isset($rule['route']) || !isset($rule['allow'])) {
                throw new \Exception('Security rule with key ' . $ruleKey . ' must contain route and allow parameters ' . var_export($rule, true));
            }

            if (
                $rule['route'] === $request->getRoute() || (
                    substr($rule['route'], strlen($rule['route']) - 2, 2) === '/*' &&
                    strpos($request->getRoute(), substr($rule['route'], 0, strlen($rule['route']) - 2)) === 0
                )
            ) {
                if (isset($rule['methods']) && !in_array($request->getMethod(), $rule['methods'])) {
                    continue;
                }
                $included = true;
                if (empty($roles)) {
                    break;
                }

                foreach ($roles as $role) {
                    if (in_array($role, $rule['allow'])) {
                        $permitted = true;
                    }
                }
            }
        }

        if ($included && !$permitted) {
            $this->sessionService->set(['previousNotAllowedPath' => $request->getPath()]);

            return new Response(null, [], [], ['Location' => '/login']);
        }

        return $this->getNext()->process($request);
    }
}
