<?php declare(strict_types=1);
namespace LightApp\Model\Validator;

use LightApp\Model\Service\SessionService;
use LightApp\Model\Validator\ValidatorInterface;
use LightApp\Model\Validator\RequestValidatorInterface;
use LightApp\Model\Validator\ArrayValidatorInterface;

class ValidatorFactory
{
    private $sessionService;
    private $csrfToken;
    private $validators;

    public function __construct(SessionService $sessionService)
    {
        $this->sessionService = $sessionService;
        $this->csrfToken = $this->generateCsrfToken();
        $this->validators = [];
    }

    public function create(string $class, array $params = []) : ValidatorInterface
    {
        $classInterfaces = class_implements($class);
        $requestValidator = in_array(RequestValidatorInterface::class, $classInterfaces);
        if (
            !in_array(ArrayValidatorInterface::class, $classInterfaces) &&
            !$requestValidator
        ) {
            throw new \Exception('Wrong class exception, ' . $class . ' has to implement ' . ArrayValidatorInterface::class . ' or ' . RequestValidatorInterface::class);
        }

        if (!isset($this->validators[$class])) {
            if ($requestValidator) {
                $this->validators[$class] = new $class($this->csrfToken, $params);
            } else {
                $this->validators[$class] = new $class($params);
            }
        }

        return $this->validators[$class];
    }

    private function generateCsrfToken() : string
    {
        $csrfToken = $this->sessionService->get(['csrfToken'])['csrfToken'];
        if ($csrfToken !== null) {
            return $csrfToken;
        }

        $value = bin2hex(random_bytes(32));
        $this->sessionService->set(['csrfToken' => $value]);

        return $value;
    }
}
