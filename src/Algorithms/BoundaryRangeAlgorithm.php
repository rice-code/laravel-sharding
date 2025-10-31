<?php

namespace Rice\LSharding\Algorithms;

use Illuminate\Support\Str;
use Rice\LSharding\BoundaryRangeSharding;

class BoundaryRangeAlgorithm extends Algorithm
{
    public function getTables(): array
    {
        /**
         * @var BoundaryRangeSharding $model
         */
        $model        = $this->model;
        $tables       = $model->queryOldTable() ? [$model->getOriginalTable()] : [];
        $shardingKeys = $this->filterKeys();
        while ($shardingKeys) {
            $tables[] = $model->getShardingTable(array_pop($shardingKeys));
        }

        return $tables;
    }

    public function getSuffix($parameters): ?string
    {
        /**
         * @var BoundaryRangeSharding $model
         */
        $model         = $this->model;
        $shardingValue = $model->getShardingValue($parameters);

        // 未获取到分片值,直接返回null
        if (is_null($shardingValue)) {
            return null;
        }
        $shardingValue = $model->getShardingValue($parameters);

        $boundaries = $this->getBoundaries();

        return $model->getShardingTable($this->getTableSuffix($shardingValue, $boundaries));
    }

    /**
     * 获取分表后缀
     */
    private function getTableSuffix($shardingValue, $boundaries): string
    {
        foreach ($boundaries as $boundary) {
            if ($shardingValue >= $boundary['start'] && $shardingValue <= $boundary['end']) {
                return $boundary['suffix'];
            }
        }

        // 未匹配到区间时抛出异常或默认处理
        throw new \InvalidArgumentException("No matching boundary for key value: {$shardingValue}");
    }

    protected function filterKeys()
    {
        /**
         * @var BoundaryRangeSharding $model
         */
        $model = $this->model;
        $keys  = [];
        if ($wheres = ($this->builder->getQuery()->wheres ?? [])) {
            foreach ($wheres as $where) {
                // 分片字段
                if (Str::after($where['column'], '.') === $model->shardingColumn()) {
                }
            }
        }

        return $keys ?: array_column($this->getBoundaries(), 'suffix');
    }

    /**
     * @return array
     */
    protected function getBoundaries(): array
    {
        /**
         * @var BoundaryRangeSharding $model
         */
        $model      = $this->model;
        $boundaries = $model->boundaries();

        // 校验边界配置
        if (empty($boundaries)) {
            throw new \InvalidArgumentException('BoundaryRangeSharding requires `boundaries` configuration.');
        }
        // 按起始值排序边界区间
        usort($boundaries, static function ($a, $b) {
            return $a['start'] - $b['start'];
        });

        return $boundaries;
    }
}
