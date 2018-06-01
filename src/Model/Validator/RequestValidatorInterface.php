<?php declare(strict_types=1);
namespace LightApp\Model\Validator;

use LightApp\Model\System\Request;

interface RequestValidatorInterface
{
    public function check(Request $request) : bool;
}
