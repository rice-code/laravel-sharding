<?php

namespace Rice\LSharding\Traits;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;

trait ReplaceTrait
{
    /**
     * 替换查询指定的表.
     *
     * @param Builder $query
     * @param string  $originalTable
     * @param $shardingTable
     * @return void
     */
    public function replaceColumns(Builder $query, string $originalTable, $shardingTable): void
    {
        $columns = $query->columns;
        if (empty($columns)) {
            return;
        }
        foreach ($columns as $idx => $column) {
            $lowerColumn = strtolower($column);
            $columnStr   = str_replace($originalTable, $shardingTable, $lowerColumn);
            $fieldInfo   = $this->getFields()[$lowerColumn];
            if ($fieldInfo->isDistinct()) {
                $columnStr = $fieldInfo->getName();
                if ($fieldInfo->getAlias()) {
                    $columnStr = sprintf('%s as %s', $columnStr, $fieldInfo->getAlias());
                }
            }
            if ($column instanceof Expression) {
                $columns[$idx] = new Expression($columnStr);

                continue;
            }
            $columns[$idx] = $columnStr;
        }
        $query->columns = $columns;
    }

    /**
     * 替换查询指定的表.
     *
     * @param Builder $query
     * @param string  $originalTable
     * @param $shardingTable
     * @return void
     */
    public function replaceWheres(Builder $query, string $originalTable, $shardingTable): void
    {
        $wheres = $query->wheres;
        if (empty($wheres)) {
            return;
        }
        foreach ($wheres as $key => $where) {
            $where['column'] = str_replace($originalTable, $shardingTable, $where['column']);
            $wheres[$key]    = $where;
        }
        $query->wheres = $wheres;
    }
}
