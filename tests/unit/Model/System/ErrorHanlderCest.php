<?php
use LightApp\Model\System\ErrorHandler;
use Codeception\Example;

class ErrorHandlerCest
{
    public $errorHandler;
    public $tmpDir;

    public function _before()
    {
        defined('APP_ROOT_DIR') || define('APP_ROOT_DIR', __DIR__ . '/../../../_data');
        $this->errorHandler = new ErrorHandler(
            'dev',
            'application/json'
        );
        $this->tmpDir = APP_ROOT_DIR . '/tmp';
    }

    public function _after(UnitTester $I)
    {
        $I->removeDirRecursively($this->tmpDir);
    }

    public function logTest(UnitTester $I)
    {
        $I->callNonPublic($this->errorHandler, 'log', [123, 'some message', 'some file', 1, 'some reason']);
        $content = file_get_contents($this->tmpDir . '/logs/php-' . date('Y-m-d') . '.log');
        $I->assertContains('some message', $content);
        $I->assertContains('some file', $content);
    }
}
