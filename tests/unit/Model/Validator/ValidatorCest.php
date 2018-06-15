<?php
use LightApp\Model\Validator\ValidatorFactory;
use LightApp\Model\Service\SessionService;
use LightApp\Model\System\Request;
use Codeception\Example;
use LightApp\Model\Validator\RequestValidatorAbstract;
use LightApp\Model\Validator\ArrayValidatorAbstract;
use Codeception\Stub;

class ValidatorCest
{
    public $requestValidator;
    public $arrayValidator;
    public $tmpDir;

    public function _before()
    {
        $sessionServiceMock = Stub::makeEmpty(
            SessionService::class,
            [
                'get' => ['csrfToken' => 'someToken']
            ]
        );

        $validatorFactory = new ValidatorFactory($sessionServiceMock);
        $this->sampleRequestValidator = $validatorFactory->create(SampleRequestValidator::class);
        $this->csrfToken = $this->sampleRequestValidator->getCsrfToken();
        $this->sampleArrayValidator = $validatorFactory->create(SampleArrayValidator::class);
    }

    public function _after(UnitTester $I)
    {
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
            ['formParam' => 123, 'csrfToken' => $this->csrfToken],
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
        $I->assertEquals(true, $this->sampleArrayValidator->check(['someParam' => 'abc']));
    }
}

class SampleRequestValidator extends RequestValidatorAbstract
{
    protected function validate(Request $request) : bool
    {
        return $request->getPayload(['formParam'])['formParam'] === 123;
    }
}

class SampleArrayValidator extends ArrayValidatorAbstract
{
    public function check(array $values) : bool
    {
        return $values['someParam'] === 'abc';
    }
}
