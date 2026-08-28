<?php

namespace Howyi\Conv\Migration\Line;

interface SeparateMigrationLineInterface
{
    /**
     * @return string[]
     */
    public function getSeparateUp(): array;

    /**
     * @return string[]
     */
    public function getSeparateDown(): array;
}
