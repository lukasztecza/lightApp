<?php declare(strict_types=1);
namespace LightApp\Model\Repository;

interface DatabaseConnectionInterface
{
    public function prepare(string $sql) : void;
    public function clean() : void;
    public function fetch(string $sql = null, array $arguments = []) : array;
    public function execute(string $sql = null, $arguments) : string;
    public function begin() : void;
    public function commit() : void;
    public function rollBack() : void;
}
