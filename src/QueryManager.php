<?php

namespace Rice\LSharding;

use Illuminate\Database\Query\Builder;

class QueryManager
{
    /**
     * 替换查询指定的表.
     *
     * @param Builder $query
     * @param string  $originalTable
     * @param $shardingTable
     * @return void
     */
    public static function replaceColumns(Builder $query, string $originalTable, $shardingTable): void
    {
        $columns = $query->columns;
        foreach ($columns as $idx => $column) {
            $columns[$idx] = str_replace($originalTable, $shardingTable, $column);
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
    public static function replaceWheres(Builder $query, string $originalTable, $shardingTable): void
    {
        $wheres = $query->wheres;
        foreach ($wheres as $key => $where) {
            $where['column'] = str_replace($originalTable, $shardingTable, $where['column']);
            $wheres[$key]    = $where;
        }
        $query->wheres = $wheres;
    }
}
