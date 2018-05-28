<?php declare(strict_types=1);
namespace TinyAppBase\Model\Command;

use TinyAppBase\Model\Command\CommandResult;

interface CommandInterface
{
    public function execute() : CommandResult;
}
