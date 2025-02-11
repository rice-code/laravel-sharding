<?php

namespace Rice\LSharding\Algorithms;

use Illuminate\Support\Str;
use Rice\LSharding\ModSharding;

class ModAlgorithm extends Algorithm
{
    public function getTables(): array
    {
        /**
         * @var ModSharding $model
         */
        $model = $this->model;

        $tables          = $model->queryOldTable() ? [$model->getOriginalTable()] : [];
        $shardingKeys    = $this->filterKeys() ?: range(0, $model->count() - 1);
        while ($shardingKeys) {
            $tables[] = $model->getShardingTable(array_pop($shardingKeys));
        }

        return $tables;
    }

    protected function filterKeys()
    {
        $keys = [];
        /**
         * @var $model ModSharding
         */
        $model = $this->model;
        if ($wheres = ($this->builder->getQuery()->wheres ?? [])) {
            foreach ($wheres as $where) {
                // 分片字段
                if (Str::after($where['column'], '.') === $model->shardingColumn()) {
                    dump($where);
                    if ($where['type'] === 'In' || $where['type'] === 'InRaw') {
                        foreach ($where['values'] as $value) {
                            $keys[] = $this->mod($value);
                        }
                        continue;
                    }
                    if (!isset($where['value'])) {
                        continue;
                    }
                    $keys[] = $this->mod($where['value']);
                }
            }
        }

        return array_unique($keys);
    }

    public function getSuffix($parameters): ?string
    {
        /**
         * @var ModSharding $model
         */
        $model         = $this->model;
        $shardingValue = $model->getShardingValue($parameters);

        // 未获取到分片值,直接返回null
        if (is_null($shardingValue)) {
            return null;
        }

        // 创建或更新使用指定字段分表
        return sprintf($model->suffixPattern(), $this->mod($shardingValue));
    }

    private function mod($value)
    {
        /**
         * @var ModSharding $model
         */
        $model         = $this->model;
        return $value % $model->count();
    }
}
