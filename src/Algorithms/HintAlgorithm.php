<?php

namespace Rice\LSharding\Algorithms;

use Rice\LSharding\HintSharding;

class HintAlgorithm extends Algorithm
{
    public function getTables(): array
    {
        /**
         * @var HintSharding $model
         */
        $model = $this->model;

        $tables = $model->queryOldTable() ? [$model->getOriginalTable()] : [];
        $shardingValue = $model::getCurrentSharding();
        
        if ($shardingValue) {
            if (is_array($shardingValue)) {
                foreach ($shardingValue as $value) {
                    $tables[] = $model->getShardingTable($value);
                }
            } else {
                $tables[] = $model->getShardingTable($shardingValue);
            }
        }

        return $tables;
    }

    protected function filterKeys()
    {
        /**
         * @var HintSharding $model
         */
        $model = $this->model;
        $shardingValue = $model::getCurrentSharding();
        
        if ($shardingValue) {
            return is_array($shardingValue) ? $shardingValue : [$shardingValue];
        }
        
        return [];
    }

    public function getSuffix($parameters): ?string
    {
        /**
         * @var HintSharding $model
         */
        $model = $this->model;
        $shardingValue = $model::getCurrentSharding();
        
        if ($shardingValue) {
            // 如果设置了多个分片，返回第一个
            if (is_array($shardingValue)) {
                $shardingValue = reset($shardingValue);
            }
            return sprintf($model->suffixPattern(), $shardingValue);
        }
        
        return null;
    }
}