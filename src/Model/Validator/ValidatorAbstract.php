<?php declare(strict_types=1);
namespace LightApp\Model\Validator;

use LightApp\Model\Validator\ValidatorInterface;

abstract class ValidatorAbstract implements ValidatorInterface
{
    protected $error = '';
    protected $params;

    public function __construct($params)
    {
        $this->params = $params;
    }

    public function getError() : string
    {
        return $this->error;
    }
}
