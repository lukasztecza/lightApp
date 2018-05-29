<?php declare(strict_types=1);
namespace TinyAppBase\Model\Validator;

interface ArrayValidatorInterface
{
    public function check(array $values) : bool;
}
