<?php

namespace Rice\LSharding\Algorithms;

use Illuminate\Support\Str;
use Rice\LSharding\InlineSharding;

class InlineAlgorithm extends Algorithm
{
    public function getTables(): array
    {
        /**
         * @var InlineSharding $model
         */
        $model = $this->model;

        $tables = $model->queryOldTable() ? [$model->getOriginalTable()] : [];
        $shardingKeys = $this->filterKeys() ?: $model->shardingTables();
        
        foreach ($shardingKeys as $key) {
            $tables[] = $model->getOriginalTable() . '_' . $key;
        }

        return $tables;
    }

    protected function filterKeys()
    {
        $keys = [];
        /**
         * @var InlineSharding $model
         */
        $model = $this->model;
        
        if ($wheres = ($this->builder->getQuery()->wheres ?? [])) {
            foreach ($wheres as $where) {
                // 提取所有可能的分片字段值
                $column = Str::after($where['column'], '.');
                if (isset($where['value'])) {
                    $keys[] = $this->evaluateExpression($model->inlineExpression(), [$column => $where['value']]);
                } else if ($where['type'] === 'In' && isset($where['values'])) {
                    foreach ($where['values'] as $value) {
                        $keys[] = $this->evaluateExpression($model->inlineExpression(), [$column => $value]);
                    }
                }
            }
        }

        return array_unique($keys);
    }

    public function getSuffix($parameters): ?string
    {
        /**
         * @var InlineSharding $model
         */
        $model = $this->model;
        
        // 提取参数中的分片字段值
        $shardingColumn = $model->shardingColumn();
        if (!isset($parameters[$shardingColumn])) {
            return null;
        }
        
        $shardingValue = $parameters[$shardingColumn];
        $result = $this->evaluateExpression($model->inlineExpression(), [$shardingColumn => $shardingValue]);
        
        return sprintf($model->suffixPattern(), $result);
    }

    /**
     * 简单的行表达式计算器
     * 支持 ${column % count} 格式的表达式
     */
    private function evaluateExpression(string $expression, array $parameters): string
    {
        // 提取表达式中的变量和操作
        if (preg_match('/\$\{(.+)\}/', $expression, $matches)) {
            $expr = $matches[1];
            
            // 简单的表达式解析和计算
            // 这里实现一个基础版本，支持简单的算术运算
            foreach ($parameters as $key => $value) {
                $expr = str_replace($key, $value, $expr);
            }
            
            // 安全地计算表达式
            try {
                // 仅允许基本的算术运算符
                if (preg_match('/^[0-9\s+\-\*\/\(\)\.]+$/', $expr)) {
                    return (string)eval("return {$expr};");
                }
            } catch (\Exception $e) {
                // 表达式计算失败
            }
        }
        
        return $expression;
    }
}