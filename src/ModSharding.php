<?php

namespace Rice\LSharding;

use Rice\LSharding\Algorithms\DatetimeAlgorithm;
use Rice\LSharding\Algorithms\ModAlgorithm;

abstract class ModSharding extends Sharding
{
    public function __construct(array $attributes = [])
    {
        $this->algorithm = new ModAlgorithm($this);
        parent::__construct($attributes);
    }
    /**
     * 分片数量
     *
     * @return int
     */
    abstract public function count(): int;

    /**
     * 分片数据源或真实表的后缀格式.
     *
     * @return mixed
     */
    abstract public function suffixPattern();

    /**
     *
     * @return string
     */
    abstract public function shardingColumn(): string;

    /**
     * 是否查询旧表（旧数据未做迁移）
     *
     * @return bool
     */
    public function queryOldTable(): bool
    {
        return false;
    }
}
