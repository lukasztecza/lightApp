<?php declare(strict_types=1);
namespace TinyAppBase\Model\Validator;

use TinyAppBase\Model\Validator\ValidatorInterface;
use TinyAppBase\Model\Validator\ValidatorAbstract;
use TinyAppBase\Model\Validator\ArrayValidatorInterface;

abstract class ArrayValidatorAbstract extends ValidatorAbstract implements ArrayValidatorInterface
{
    abstract public function check(array $values) : bool
}
