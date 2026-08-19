<?php

namespace Howyi\Conv\Structure;

class TableTTLStructure
{
    public const DEFAULT_JOB_INTERVAL = '1h';

    /** @var string */
    private $expression;

    /** @var string|null */
    private $enable;

    /** @var string|null */
    private $jobInterval;

    public function __construct(
        string $expression,
        ?string $enable = null,
        ?string $jobInterval = null
    ) {
        $this->expression = trim($expression);
        $this->enable = is_null($enable) ? null : strtoupper($enable);
        $this->jobInterval = $jobInterval;
    }

    public function getExpression(): string
    {
        return $this->expression;
    }

    public function getEnable(): ?string
    {
        return $this->enable;
    }

    public function getJobInterval(): ?string
    {
        return $this->jobInterval;
    }

    public function isChanged(?TableTTLStructure $target): bool
    {
        if (is_null($target)) {
            return true;
        }
        return $this->expression !== $target->expression
            || $this->normalizedEnable() !== $target->normalizedEnable()
            || $this->normalizedJobInterval() !== $target->normalizedJobInterval();
    }

    public function toQuery(
        bool $includeDefaultEnable = false,
        bool $includeDefaultJobInterval = false
    ): string {
        $query = $this->toAlterQuery($includeDefaultEnable);
        if (!is_null($this->enable)) {
            $query .= " TTL_ENABLE = '{$this->enable}'";
        } elseif ($includeDefaultEnable) {
            $query .= " TTL_ENABLE = 'ON'";
        }
        if (!is_null($this->jobInterval)) {
            $query .= " TTL_JOB_INTERVAL = '" . $this->escapeString($this->jobInterval) . "'";
        } elseif ($includeDefaultJobInterval) {
            $query .= " TTL_JOB_INTERVAL = '" . self::DEFAULT_JOB_INTERVAL . "'";
        }
        return $query;
    }

    public function toAlterQuery(bool $includeDefaultEnable = false): string
    {
        $query = 'TTL = ' . $this->expression;
        if (!is_null($this->enable)) {
            $query .= " TTL_ENABLE = '{$this->enable}'";
        } elseif ($includeDefaultEnable) {
            $query .= " TTL_ENABLE = 'ON'";
        }
        return $query;
    }

    private function normalizedEnable(): string
    {
        return is_null($this->enable) ? 'ON' : $this->enable;
    }

    private function normalizedJobInterval(): string
    {
        return is_null($this->jobInterval)
            ? self::DEFAULT_JOB_INTERVAL
            : $this->jobInterval;
    }

    private function escapeString(string $value): string
    {
        return str_replace("'", "''", $value);
    }
}
