<?php declare(strict_types=1);
namespace TinyAppBase\Model\Validator;

use TinyAppBase\Model\Validator\ValidatorInterface;

abstract class ValidatorAbstract implements ValidatorInterface
{
    protected $error = '';

    public function getError() : string
    {
        return $this->error;
    }
}
