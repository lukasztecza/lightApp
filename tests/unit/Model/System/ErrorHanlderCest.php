<?php
use LightApp\Model\System\ErrorHandler;
use Codeception\Example;

class ErrorHandlerCest
{
    public $errorHandler;

    public function _before()
    {
        define('APP_ROOT_DIR', __DIR__ . '/../../_data');
        $this->errorHandler = new ErrorHandler(
            'prod',
            'application/json'
        );
    }

    public function _after()
    {
        //remove created dirs
    }

    public function logTest(UnitTester $I)
    {
//        ob_start();

        $I->callNonPublic($this->response, 'log', [123, 'some message', 'some file', 'some line', 'some reason']);

  //      $result = ob_get_contents();
    //    ob_clean();

        //$I->assertEquals($result, $value);
    }
}
