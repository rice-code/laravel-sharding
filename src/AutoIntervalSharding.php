<?php

namespace Rice\LSharding;

use Rice\LSharding\Algorithms\AutoIntervalAlgorithm;

/**
 * 基于可变时间范围的分片策略
 * 支持根据时间动态调整分片区间
 */
abstract class AutoIntervalSharding extends Sharding
{
    public function __construct(array $attributes = [])
    {
        $this->algorithm = new AutoIntervalAlgorithm($this);
        parent::__construct($attributes);
    }

    /**
     * 时间分片下界值
     *
     * @return string
     */
    abstract public function lower();

    /**
     * 分片时间间隔表达式
     * 格式示例：1d (1天), 1w (1周), 1M (1月), 1y (1年)
     *
     * @return string
     */
    abstract public function interval();

    /**
     * 分片键时间格式
     *
     * @return string
     */
    public function dateTimeFormat(): string
    {
        return 'Ymd';
    }
}