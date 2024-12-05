<?php

namespace Rice\LSharding\Traits;

use Carbon\Carbon;

trait GetTrait
{
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

        return $parameters[$this->shardingKey] ?? $this->attributes[$this->shardingKey] ?? null;
    }
}
