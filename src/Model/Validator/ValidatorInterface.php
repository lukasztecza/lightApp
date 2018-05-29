<?php declare(strict_types=1);
namespace TinyAppBase\Model\Validator;

interface ValidatorInterface
{
    public function getError() : string;
}
