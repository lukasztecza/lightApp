<?php
use LightApp\Model\System\Project;
use Codeception\Example;

class ProjectCest
{
    public $router;

    public function _before()
    {
        //set app root path and create config files and create project instance
    }

    public function _after()
    {
        //remove created dirs
    }

    public function runTest(UnitTester $I)
    {
        $I->assertEquals(1,1);
    }
}
