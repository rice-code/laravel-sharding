<?php

namespace Rice\LSharding;

use Rice\LSharding\Algorithms\InlineAlgorithm;

/**
 * 基于行表达式的分片策略
 * 支持通过表达式动态计算分片表
 */
abstract class InlineSharding extends Sharding
{
    public function __construct(array $attributes = [])
    {
        $this->algorithm = new InlineAlgorithm($this);
        parent::__construct($attributes);
    }

    /**
     * 行表达式
     * 格式示例：${user_id % 4}
     *
     * @return string
     */
    abstract public function inlineExpression(): string;

    /**
     * 分片表列表
     *
     * @return array
     */
    abstract public function shardingTables(): array;
}