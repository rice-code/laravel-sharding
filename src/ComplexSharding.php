<?php

namespace Rice\LSharding;

use Rice\LSharding\Algorithms\ComplexAlgorithm;

/**
 * 复合分片策略
 * 支持使用多个字段进行分片计算
 */
abstract class ComplexSharding extends Sharding
{
    public function __construct(array $attributes = [])
    {
        $this->algorithm = new ComplexAlgorithm($this);
        parent::__construct($attributes);
    }

    /**
     * 获取所有分片列
     *
     * @return array
     */
    abstract public function shardingColumns(): array;

    /**
     * 根据多个分片键计算分片表名
     *
     * @param array $shardingValues 分片键值对
     * @return string
     */
    abstract public function calculateShardingKey(array $shardingValues): string;
}