<?php declare(strict_types=1);
namespace LightApp\Model\Validator;

interface ValidatorInterface
{
    public function getError() : string;
}
