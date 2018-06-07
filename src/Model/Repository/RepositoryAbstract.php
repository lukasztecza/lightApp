<?php declare(strict_types=1);
namespace LightApp\Model\Repository;

use LightApp\Model\Repository\RepositoryInterface;
use LightApp\Model\Repository\DatabaseConnectionInterface;

abstract class RepositoryAbstract implements RepositoryInterface
{
    private $write;
    private $read;
    private $counter;

    public function __construct(DatabaseConnectionInterface $write, DatabaseConnectionInterface $read)
    {
        $this->write = $write;
        $this->read = $read;
        $this->counter = 1;
    }

    public function getWrite() : DatabaseConnectionInterface
    {
        return $this->write;
    }

    public function getRead() : DatabaseConnectionInterface
    {
        return $this->read;
    }

    protected function getPages(string $sql, array $arguments, int $perPage) : int
    {
        if ($perPage < 1) {
            throw new \Exception('Need at least one per page');
        }

        $total = $this->read->fetch($sql, $arguments);
        if (!empty($total[0]['count'])) {
            $pages = $total[0]['count'] / $perPage;
            return (int) $pages < $pages ? $pages + 1 : $pages;
        }

        return 0;
    }

    protected function getInPlaceholdersAndAddParams(array $values, array &$params) : string
    {
        $placeholders = [];
        foreach ($values as $value) {
            $placeholder = ':value' . $this->counter;
            $placeholders[] = $placeholder;
            $params[ltrim($placeholder, ':')] = $value;
            $this->counter++;
        }

        return implode(', ', $placeholders);
    }
}
