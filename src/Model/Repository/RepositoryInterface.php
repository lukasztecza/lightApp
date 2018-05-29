<?php declare(strict_types=1);
namespace TinyAppBase\Model\Repository;

use TinyAppBase\Model\Repository\DatabaseConnectionInterface;

interface RepositoryInterface
{
    public function getWrite() : DatabaseConnectionInterface;
    public function getRead() : DatabaseConnectionInterface;
}
