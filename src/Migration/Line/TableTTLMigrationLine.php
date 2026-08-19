<?php

namespace Howyi\Conv\Migration\Line;

use Howyi\Conv\Structure\TableTTLStructure;

class TableTTLMigrationLine extends AbstractMigrationLine
{
    /**
     * @param TableTTLStructure|null $before
     * @param TableTTLStructure|null $after
     */
    public function __construct(
        ?TableTTLStructure $before,
        ?TableTTLStructure $after
    ) {
        if ($this->isDefinitionChanged($before, $after)) {
            $this->upLineList[] = $this->toQuery(
                $after,
                $this->shouldIncludeDefaultJobInterval($before, $after)
            );
            $this->downLineList[] = $this->toQuery(
                $before,
                $this->shouldIncludeDefaultJobInterval($after, $before)
            );
            return;
        }

        if ($this->getEnable($before) !== $this->getEnable($after)) {
            $this->upLineList[] = $this->toEnableQuery($after);
            $this->downLineList[] = $this->toEnableQuery($before);
        }
        if ($this->getJobInterval($before) !== $this->getJobInterval($after)) {
            $this->upLineList[] = $this->toJobIntervalQuery($after);
            $this->downLineList[] = $this->toJobIntervalQuery($before);
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

    private function toQuery(
        ?TableTTLStructure $ttl,
        bool $includeDefaultJobInterval = false
    ): string {
        return is_null($ttl)
            ? 'REMOVE TTL'
            : $ttl->toQuery($includeDefaultJobInterval);
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
