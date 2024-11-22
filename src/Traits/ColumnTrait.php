<?php

namespace Rice\LSharding\Traits;

use Rice\LSharding\Column;
use Illuminate\Support\Str;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;

trait ColumnTrait
{
    /**
     * 补列.
     *
     * @param Builder $query
     * @param         $shardingTable
     * @return void
     */
    public function addColumns(Builder $query, $shardingTable): void
    {
        $columns      = array_keys($this->fields);
        if (array_intersect($columns, ['*', $shardingTable . '*'])) {
            return;
        }
        $addedColumns = [];
        foreach ($query->groups as $groupField) {
            $groupField = strtolower(trim($groupField));
            if (!in_array($groupField, $columns, true)) {
                // 别名不补列
                if (isset($this->alias[$groupField])) {
                    continue;
                }
                $fieldName                = Str::contains('.', $groupField) ? $groupField : $shardingTable . '.' . $groupField;
                $this->fields[$fieldName] = new Column($fieldName);
                $query->columns[]         = $fieldName;
                $addedColumns[]           = $groupField;
                $addedColumns[]           = $fieldName;
            }
        }

        foreach ($query->orders as $orderField) {
            $orderField = strtolower(trim($orderField['column']));
            if (!in_array($orderField, $columns, true) && !in_array($orderField, $addedColumns, true)) {
                $fieldName                = Str::contains('.', $orderField) ? $orderField : $shardingTable . '.' . $orderField;
                // 别名不补列
                if (isset($this->alias[$orderField])) {
                    continue;
                }
                $this->fields[$fieldName] = new Column($fieldName);
                $query->columns[]          = $fieldName;
                $addedColumns[]            = $orderField;
                $addedColumns[]            = $fieldName;
            }
        }
    }

    /**
     * 替换查询指定的表.
     *
     * @param Builder $query
     * @param string  $originalTable
     * @param         $shardingTable
     * @return void
     */
    public function replaceColumns(Builder $query, string $originalTable, $shardingTable): void
    {
        $columns = $query->columns;
        if (empty($columns)) {
            return;
        }
        foreach ($columns as $idx => $column) {
            $lowerColumn = strtolower(trim($column));
            $tableName   = Str::before($lowerColumn, '.');
            $columnStr   = $tableName === $shardingTable ? $lowerColumn : str_replace($originalTable, $shardingTable, $lowerColumn);
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
     * @param         $shardingTable
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
