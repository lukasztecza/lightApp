<?php declare(strict_types=1);
namespace TinyAppBase\Model\Validator;

use TinyAppBase\Model\System\Request;

interface RequestValidatorInterface
{
    public function check(Request $request) : bool;
}
