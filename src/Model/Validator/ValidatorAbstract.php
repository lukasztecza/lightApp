<?php declare(strict_types=1);
namespace LightApp\Model\Validator;

use LightApp\Model\Validator\ValidatorInterface;

abstract class ValidatorAbstract implements ValidatorInterface
{
    protected $error = '';

    public function getError() : string
    {
        return $this->error;
    }
}
