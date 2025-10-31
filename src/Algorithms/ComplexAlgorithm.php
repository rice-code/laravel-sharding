<?php

namespace Rice\LSharding\Algorithms;

use Illuminate\Support\Str;
use Rice\LSharding\ComplexSharding;

class ComplexAlgorithm extends Algorithm
{
    public function getTables(): array
    {
        /**
         * @var ComplexSharding $model
         */
        $model = $this->model;

        $tables = $model->queryOldTable() ? [$model->getOriginalTable()] : [];
        $shardingKeys = $this->filterKeys();
        
        if ($shardingKeys) {
            foreach ($shardingKeys as $key) {
                $tables[] = $model->getShardingTable($key);
            }
        } else {
            // 如果没有过滤条件，返回所有可能的表（这里需要根据实际情况调整）
            // 实际应用中应该根据配置或动态计算所有可能的分片
        }

        return $tables;
    }

    protected function filterKeys()
    {
        $keys = [];
        /**
         * @var ComplexSharding $model
         */
        $model = $this->model;
        $shardingColumns = $model->shardingColumns();
        $shardingValues = [];
        
        if ($wheres = ($this->builder->getQuery()->wheres ?? [])) {
            foreach ($wheres as $where) {
                $column = Str::after($where['column'], '.');
                // 收集分片字段的值
                if (in_array($column, $shardingColumns) && isset($where['value'])) {
                    $shardingValues[$column] = $where['value'];
                }
            }
        }
        
        // 如果收集到了所有必要的分片字段值，计算分片键
        if (count($shardingValues) === count($shardingColumns)) {
            $keys[] = $model->calculateShardingKey($shardingValues);
        }

        return $keys;
    }

    public function getSuffix($parameters): ?string
    {
        /**
         * @var ComplexSharding $model
         */
        $model = $this->model;
        $shardingColumns = $model->shardingColumns();
        $shardingValues = [];
        
        // 从参数中提取所有分片字段的值
        foreach ($shardingColumns as $column) {
            if (isset($parameters[$column])) {
                $shardingValues[$column] = $parameters[$column];
            } else {
                // 如果缺少任何一个分片字段，返回null
                return null;
            }
        }
        
        $shardingKey = $model->calculateShardingKey($shardingValues);
        return sprintf($model->suffixPattern(), $shardingKey);
    }
}