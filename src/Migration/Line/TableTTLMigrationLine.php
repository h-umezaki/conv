<?php

namespace Howyi\Conv\Migration\Line;

use Howyi\Conv\Structure\TableTTLStructure;

/**
 * ALTER TABLE ~ TTL ~
 */
class TableTTLMigrationLine extends AbstractMigrationLine implements SeparateMigrationLineInterface
{
    private $separateUpLineList = [];
    private $separateDownLineList = [];

    /**
     * @param TableTTLStructure|null $before
     * @param TableTTLStructure|null $after
     */
    public function __construct(
        ?TableTTLStructure $before,
        ?TableTTLStructure $after
    ) {
        if (
            $this->isDefinitionChanged($before, $after)
            || $this->getEnable($before) !== $this->getEnable($after)
            || $this->getJobInterval($before) !== $this->getJobInterval($after)
        ) {
            $this->addDefinitionLine(
                $this->upLineList,
                $after,
                $this->shouldIncludeDefaultEnable($before, $after),
                $this->shouldIncludeDefaultJobInterval($before, $after)
            );
            $this->addDefinitionLine(
                $this->downLineList,
                $before,
                $this->shouldIncludeDefaultEnable($after, $before),
                $this->shouldIncludeDefaultJobInterval($after, $before)
            );
        }
    }

    /**
     * @return string[]
     */
    public function getSeparateUp(): array
    {
        return $this->separateUpLineList;
    }

    /**
     * @return string[]
     */
    public function getSeparateDown(): array
    {
        return $this->separateDownLineList;
    }

    private function addDefinitionLine(
        array &$lineList,
        ?TableTTLStructure $ttl,
        bool $includeDefaultEnable,
        bool $includeDefaultJobInterval
    ): void {
        if (is_null($ttl)) {
            $lineList[] = 'REMOVE TTL';
            return;
        }
        $lineList[] = $ttl->toQuery(
            $includeDefaultEnable,
            $includeDefaultJobInterval
        );
    }

    private function isDefinitionChanged(
        ?TableTTLStructure $before,
        ?TableTTLStructure $after
    ): bool {
        if (is_null($before) || is_null($after)) {
            return true;
        }
        return $before->getExpression() !== $after->getExpression();
    }

    private function shouldIncludeDefaultEnable(
        ?TableTTLStructure $before,
        ?TableTTLStructure $after
    ): bool {
        return !is_null($before)
            && !is_null($after)
            && !is_null($before->getEnable())
            && is_null($after->getEnable());
    }

    private function shouldIncludeDefaultJobInterval(
        ?TableTTLStructure $before,
        ?TableTTLStructure $after
    ): bool {
        return !is_null($before)
            && !is_null($after)
            && !is_null($before->getJobInterval())
            && is_null($after->getJobInterval());
    }

    private function getEnable(?TableTTLStructure $ttl): string
    {
        return is_null($ttl) || is_null($ttl->getEnable())
            ? 'ON'
            : $ttl->getEnable();
    }

    private function getJobInterval(?TableTTLStructure $ttl): ?string
    {
        return is_null($ttl) ? null : $ttl->getJobInterval();
    }
}
