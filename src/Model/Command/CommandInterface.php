<?php
namespace TinyAppBase\Model\Command;

use TinyAppBase\Model\Command\CommandResult;

interface CommandInterface
{
    public function execute() : CommandResult;
}
