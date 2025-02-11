<?php

namespace Rice\LSharding\Algorithms;

use Illuminate\Support\Str;
use Rice\LSharding\HashModSharding;

class HashModAlgorithm extends Algorithm
{
    public function getTables(): array
    {
        /**
         * @var HashModSharding $model
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
         * @var $model HashModSharding
         */
        $model = $this->model;
        if ($wheres = ($this->builder->getQuery()->wheres ?? [])) {
            foreach ($wheres as $where) {
                // 分片字段
                if (Str::after($where['column'], '.') === $model->shardingColumn()) {
                    if ($where['type'] === 'In') {
                        foreach ($where['values'] as $value) {
                            $keys[] = $this->mod($value);
                        }
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
         * @var HashModSharding $model
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
         * @var HashModSharding $model
         */
        $model         = $this->model;
        return crc32(hash('sha256', $value)) % $model->count();
    }
}
