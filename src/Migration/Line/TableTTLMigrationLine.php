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
        if ($this->isDefinitionChanged($before, $after)) {
            $this->addDefinitionLine(
                $this->upLineList,
                $this->separateUpLineList,
                $after,
                $this->shouldIncludeDefaultEnable($before, $after),
                $this->shouldIncludeDefaultJobInterval($before, $after)
            );
            $this->addDefinitionLine(
                $this->downLineList,
                $this->separateDownLineList,
                $before,
                $this->shouldIncludeDefaultEnable($after, $before),
                $this->shouldIncludeDefaultJobInterval($after, $before)
            );
            return;
        }

        if ($this->getEnable($before) !== $this->getEnable($after)) {
            $this->upLineList[] = $this->toEnableQuery($after);
            $this->downLineList[] = $this->toEnableQuery($before);
        }
        if ($this->getJobInterval($before) !== $this->getJobInterval($after)) {
            $this->separateUpLineList[] = $this->toJobIntervalQuery($after);
            $this->separateDownLineList[] = $this->toJobIntervalQuery($before);
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
        array &$separateLineList,
        ?TableTTLStructure $ttl,
        bool $includeDefaultEnable,
        bool $includeDefaultJobInterval
    ): void {
        if (is_null($ttl)) {
            $lineList[] = 'REMOVE TTL';
            return;
        }
        $lineList[] = $ttl->toAlterQuery($includeDefaultEnable);
        if ($includeDefaultJobInterval || !is_null($ttl->getJobInterval())) {
            $separateLineList[] = $this->toJobIntervalQuery($ttl);
        }
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

    private function toEnableQuery(?TableTTLStructure $ttl): string
    {
        return "TTL_ENABLE = '" . $this->getEnable($ttl) . "'";
    }

    private function toJobIntervalQuery(?TableTTLStructure $ttl): string
    {
        if (is_null($ttl) || is_null($ttl->getJobInterval())) {
            // REMOVE TTL_JOB_INTERVAL 構文がないため、TiDB のデフォルト値に戻す。
            return "TTL_JOB_INTERVAL = '" . TableTTLStructure::DEFAULT_JOB_INTERVAL . "'";
        }
        $jobInterval = str_replace("'", "''", $ttl->getJobInterval());
        return "TTL_JOB_INTERVAL = '$jobInterval'";
    }
}
