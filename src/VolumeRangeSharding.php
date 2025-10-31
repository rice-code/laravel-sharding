<?php

namespace Rice\LSharding;

use Rice\LSharding\Algorithms\VolumeRangeAlgorithm;

/**
 * 基于分片容量的范围分片策略
 * 按照固定容量划分数据范围
 */
abstract class VolumeRangeSharding extends Sharding
{
    public function __construct(array $attributes = [])
    {
        $this->algorithm = new VolumeRangeAlgorithm($this);
        parent::__construct($attributes);
    }

    /**
     * 每个分片的容量
     *
     * @return int
     */
    abstract public function shardingVolume(): int;

    /**
     * 分片的起始值
     *
     * @return int
     */
    abstract public function startValue(): int;

    /**
     * 分片的最大值（可选）
     *
     * @return int|null
     */
    public function maxValue(): ?int
    {
        return null;
    }
}