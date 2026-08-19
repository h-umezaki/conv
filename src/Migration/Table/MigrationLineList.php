<?php

namespace Howyi\Conv\Migration\Table;

use Howyi\Conv\Migration\Line\MigrationLineInterface;
use Howyi\Conv\Migration\Line\SeparateMigrationLineInterface;

class MigrationLineList
{
    protected $migrationLineList = [];

    /**
     * @param MigrationLineInterface $migrationLine
     */
    public function add(MigrationLineInterface $migrationLine)
    {
        return $this->migrationLineList[] = $migrationLine;
    }


    /**
     * @return bool
     */
    public function isMigratable(): bool
    {
        return !empty($this->migrationLineList);
    }

    /**
     * @return string
     */
    public function getUp(): string
    {
        $upLineList = [];
        foreach ($this->migrationLineList as $migrationLine) {
            $upLineList = array_merge($upLineList, $migrationLine->getUp());
        }
        return '  ' . join(',' . PHP_EOL . '  ', $upLineList);
    }

    /**
     * @return string
     */
    public function getDown(): string
    {
        $downLineList = [];
        foreach (array_reverse($this->migrationLineList) as $migrationLine) {
            $downLineList = array_merge($downLineList, $migrationLine->getDown());
        }
        return '  ' . join(',' . PHP_EOL . '  ', $downLineList);
    }

    /**
     * @return string[]
     */
    public function getSeparateUp(): array
    {
        $separateUpLineList = [];
        foreach ($this->migrationLineList as $migrationLine) {
            if ($migrationLine instanceof SeparateMigrationLineInterface) {
                $separateUpLineList = array_merge(
                    $separateUpLineList,
                    $migrationLine->getSeparateUp()
                );
            }
        }
        return $separateUpLineList;
    }

    /**
     * @return string[]
     */
    public function getSeparateDown(): array
    {
        $separateDownLineList = [];
        foreach (array_reverse($this->migrationLineList) as $migrationLine) {
            if ($migrationLine instanceof SeparateMigrationLineInterface) {
                $separateDownLineList = array_merge(
                    $separateDownLineList,
                    $migrationLine->getSeparateDown()
                );
            }
        }
        return $separateDownLineList;
    }
}
