<?php declare(strict_types=1);
namespace LightApp\Model\Command;

use LightApp\Model\Command\CommandResult;

interface CommandInterface
{
    public function execute() : CommandResult;
}
