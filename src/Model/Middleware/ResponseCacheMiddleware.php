<?php declare(strict_types=1);
namespace TinyAppBase\Model\Middleware;

use TinyAppBase\Model\Middleware\MiddlewareAbstract;
use TinyAppBase\Model\System\Request;
use TinyAppBase\Model\System\Response;

class ResponseCacheMiddleware extends MiddlewareAbstract
{
    private $cacheList;

    private const CACHE_PATH = APP_ROOT_DIR . '/tmp/cache';

    public function __construct(MiddlewareInterface $next, array $cacheList)
    {
        parent::__construct($next);
        $this->cacheList = $cacheList;
    }

    public function process(Request $request) : Response
    {
        $included = false;
        if ($request->getMethod() === 'GET') {
            foreach ($this->cacheList as $ruleKey => $rule) {
                if (!isset($rule['route']) || !isset($rule['time'])) {
                    throw new \Exception('Cache rule with key ' . $ruleKey . ' must contain route and time parameters ' . var_export($rule, true));
                }

                if ($rule['route'] === $request->getRoute()) {
                    $included = true;
                    break;
                }
            }
        }

        $cacheFile = self::CACHE_PATH . '/' . md5($request->getRoute() . $request->getPath() . json_encode($request->getQuery())) . '.php';
        if ($included && file_exists($cacheFile) && (time() - $rule['time'] < filemtime($cacheFile))) {
            return unserialize(file_get_contents($cacheFile));
        }

        $response = $this->getNext()->process($request);
        if ($included) {
            if (!file_exists(self::CACHE_PATH)) {
                mkdir(self::CACHE_PATH, 0775, true);
            }
            file_put_contents($cacheFile, serialize($response));
        }

        return $response;
    }
}
