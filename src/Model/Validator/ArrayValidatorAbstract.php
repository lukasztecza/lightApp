<?php declare(strict_types=1);
namespace LightApp\Model\Validator;

use LightApp\Model\Validator\ValidatorAbstract;
use LightApp\Model\Validator\ArrayValidatorInterface;

abstract class ArrayValidatorAbstract extends ValidatorAbstract implements ArrayValidatorInterface
{
    abstract public function check(array $values) : bool;
}
