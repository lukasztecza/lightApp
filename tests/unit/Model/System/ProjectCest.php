<?php
use LightApp\Model\System\Project;
use LightApp\Model\Middleware\MiddlewareAbstract;
use LightApp\Controller\ControllerInterface;
use LightApp\Controller\ControllerAbstract;
use LightApp\Model\System\Request;
use LightApp\Model\System\Response;
use Codeception\Example;

class ProjectCest
{
    public $project;
    public $srcDir;

    public function _before()
    {
        defined('APP_ROOT_DIR') || define('APP_ROOT_DIR', __DIR__ . '/../../../_data');
        $this->project = new Project();
        $this->srcDir = APP_ROOT_DIR . '/src';
        mkdir($this->srcDir);
        mkdir($this->srcDir . '/Config');

        file_put_contents($this->srcDir . '/Config/parameters.json', json_encode([
            'environment' => 'dev'
        ]));
        file_put_contents($this->srcDir . '/Config/settings.json', json_encode([
            'defaultContentType' => 'application/json',
            'applicationStartingPoint' => 'someMiddleware',
        ]));
        file_put_contents($this->srcDir . '/Config/routes.json', json_encode([
            0 => [
                'path'=> '/',
                'controller'=> 'someController',
                'action'=> 'details'
            ]
        ]));
        file_put_contents($this->srcDir . '/Config/dependencies.json', json_encode([
            'someMiddleware' => [
                'class'=> 'SomeMiddleware',
                'inject' => [
                    '%routedController%',
                    '%routedAction%'
                ]
            ],
            'someController' => [
                'class' => 'SomeController'
            ]
        ]));
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['SERVER_PORT'] = 80;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';
        //set app root path and create config files and create project instance
    }

    public function _after(UnitTester $I)
    {
        $I->removeDirRecursively($this->srcDir);
        unset($_SERVER['SERVER_NAME']);
        unset($_SERVER['SERVER_PORT']);
        unset($_SERVER['REQUEST_METHOD']);
        unset($_SERVER['REQUEST_URI']);
    }

    public function runTest(UnitTester $I)
    {
        $reaponse = $this->project->run();
        $I->assertEquals(1,1);
    }
}

class SomeController extends ControllerAbstract{
    public function details(Request $request) : Response
    {
        return $this->jsonResponse(['one' => 1]);
    }
}

class SomeMiddleware extends MiddlewareAbstract{
    private $controller;
    private $action;
    public function __construct(ControllerInterface $controller, $action)
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
