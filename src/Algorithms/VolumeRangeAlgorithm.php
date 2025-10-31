<?php

namespace Rice\LSharding\Algorithms;

use Illuminate\Support\Str;
use Rice\LSharding\VolumeRangeSharding;

class VolumeRangeAlgorithm extends Algorithm
{
    public function getTables(): array
    {
        /**
         * @var VolumeRangeSharding $model
         */
        $model = $this->model;

        $tables = $model->queryOldTable() ? [$model->getOriginalTable()] : [];
        $shardingKeys = $this->filterKeys() ?: $this->getAllShardingKeys();
        
        foreach ($shardingKeys as $key) {
            $tables[] = $model->getShardingTable($key);
        }

        return $tables;
    }

    protected function filterKeys()
    {
        $keys = [];
        /**
         * @var VolumeRangeSharding $model
         */
        $model = $this->model;
        
        if ($wheres = ($this->builder->getQuery()->wheres ?? [])) {
            foreach ($wheres as $where) {
                // 分片字段
                if (Str::after($where['column'], '.') === $model->shardingColumn()) {
                    if ($where['type'] === 'In' || $where['type'] === 'InRaw') {
                        foreach ($where['values'] as $value) {
                            $keys[] = $this->calculateShardingKey($value);
                        }
                        continue;
                    }
                    if (isset($where['value'])) {
                        $keys[] = $this->calculateShardingKey($where['value']);
                    }
                }
            }
        }

        return array_unique($keys);
    }

    public function getSuffix($parameters): ?string
    {
        /**
         * @var VolumeRangeSharding $model
         */
        $model = $this->model;
        $shardingValue = $model->getShardingValue($parameters);

        // 未获取到分片值,直接返回null
        if (is_null($shardingValue)) {
            return null;
        }

        $shardingKey = $this->calculateShardingKey($shardingValue);
        return sprintf($model->suffixPattern(), $shardingKey);
    }

    /**
     * 根据分片值计算分片键
     */
    private function calculateShardingKey($value): int
    {
        /**
         * @var VolumeRangeSharding $model
         */
        $model = $this->model;
        $startValue = $model->startValue();
        $volume = $model->shardingVolume();
        
        if ($value < $startValue) {
            return 0;
        }
        
        return (int)floor(($value - $startValue) / $volume);
    }

    /**
     * 获取所有可能的分片键
     */
    private function getAllShardingKeys(): array
    {
        /**
         * @var VolumeRangeSharding $model
         */
        $model = $this->model;
        $keys = [];
        
        $startKey = 0;
        $maxValue = $model->maxValue();
        
        if ($maxValue) {
            $maxKey = (int)floor(($maxValue - $model->startValue()) / $model->shardingVolume());
            for ($i = $startKey; $i <= $maxKey; $i++) {
                $keys[] = $i;
            }
        } else {
            // 如果没有设置最大值，返回一定数量的分片键
            // 实际应用中应该根据实际情况调整
            for ($i = $startKey; $i < 10; $i++) {
                $keys[] = $i;
            }
        }
        
        return $keys;
    }
}