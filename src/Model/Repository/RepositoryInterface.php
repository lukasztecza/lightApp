<?php declare(strict_types=1);
namespace LightApp\Model\Repository;

use LightApp\Model\Repository\DatabaseConnectionInterface;

interface RepositoryInterface
{
    public function getWrite() : DatabaseConnectionInterface;
    public function getRead() : DatabaseConnectionInterface;
}
