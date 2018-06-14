<?php
use LightApp\Model\Validator\ValidatorFactory;
use LightApp\Model\Service\SessionService;
use LightApp\Model\System\Request;
use Codeception\Example;
use LightApp\Model\Validator\RequestValidatorAbstract;
use LightApp\Model\Validator\ArrayValidatorAbstract;

class ValidatorCest
{
    public $requestValidator;
    public $arrayValidator;
    public $tmpDir;

    public function _before()
    {
        defined('APP_ROOT_DIR') || define('APP_ROOT_DIR', __DIR__ . '/../../../_data');
        $this->tmpDir = APP_ROOT_DIR . '/tmp';

        $validatorFactory = new ValidatorFactory(new SessionService());
        $this->sampleRequestValidator = $validatorFactory->create(SampleRequestValidator::class);
        $this->csrfToken = $this->sampleRequestValidator->getCsrfToken();
        $this->sampleArrayValidator = $validatorFactory->create(SampleArrayValidator::class);
    }

    public function _after(UnitTester $I)
    {
        $I->removeDirRecursively($this->tmpDir);
    }

    public function testRequestValidator(UnitTester $I)
    {
        $request = new Request(
            'localhost',
            '/',
            '/',
            [],
            'POST',
            [],
            ['formParam' => '123', 'csrfToken' => $this->csrfToken],
            [],
            '',
            [],
            ['HTTP_ORIGIN' => 'localhost'],
            '',
            '',
            ''
        );

        $I->assertEquals(true, $this->sampleRequestValidator->check($request));
    }

    public function testArrayValidator(UnitTester $I)
    {
        $I->assertEquals(true, $this->sampleArrayValidator->check(['someParam' => 123]));
    }
}

class SampleRequestValidator extends RequestValidatorAbstract
{
    protected function validate(Request $request) : bool
    {
        $request->getPayload();
        return true;
    }
}

class SampleArrayValidator extends ArrayValidatorAbstract
{
    public function check(array $values) : bool
    {
        return $values['someParam'] === 123;
    }
}
