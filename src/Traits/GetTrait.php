<?php

namespace Rice\LSharding\Traits;

use Carbon\Carbon;

trait GetTrait
{
    public function getModel()
    {
        return $this->model;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function getOriginalTable(): string
    {
        return $this->table;
    }

    public function getTables(): array
    {
        return $this->algorithm->getTables();
    }

    /**
     * @param $parameters
     * @return mixed|null
     */
    public function getShardingValue($parameters)
    {
        if (is_object($parameters)) {
            return null;
        }

        return $parameters[$this->shardingColumn()] ?? $this->attributes[$this->shardingColumn()] ?? null;
    }

    public function getShardingTable($suffix): string
    {
        return sprintf('%s_%s', $this->getOriginalTable(), $suffix);
    }
}
