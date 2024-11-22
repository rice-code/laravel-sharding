<?php

namespace Rice\LSharding;

use Illuminate\Support\Str;

class Column
{
    protected ?string $column;
    protected ?string $name;
    protected ?string $func;
    protected ?string $aggFunc;

    protected ?string $alias;
    protected bool $isDistinct;

    public function __construct(string $column)
    {
        $lowerColumn  = strtolower(trim($column));
        $this->column = $lowerColumn;
        $isDistinct   = Str::contains($column, 'distinct');
        $segments     = preg_split('/\s+as\s+/i', $column);
        preg_match('/(count|sum|max|min|avg)\s*\((?:distinct)*(.*)\s*\)\s*/i', $lowerColumn, $matches);
        // 聚合函数
        if (3 === count($matches)) {
            $this->name       = trim($matches[2]);
            $this->func       = $segments[0];
            $this->aggFunc    = trim($matches[1]);
            $this->alias      = $segments[1] ?? null;
            $this->isDistinct = $isDistinct;

            return;
        }

        $this->name       = $segments[0];
        $this->func       = null;
        $this->aggFunc    = null;
        $this->alias      = $segments[1] ?? null;
        $this->isDistinct = $isDistinct;
    }


    public function getSelectColumn()
    {
        // 聚合函数
        if ($this->aggFunc) {
            if ($this->alias) {
                if ($this->isDistinct) {
                    return sprintf('%s(%s %s) as %s', $this->aggFunc, 'distinct', $this->alias, $this->alias);
                }
                // 非去重聚合函数直接累加分表结果
                return sprintf('sum(%s) as %s', $this->alias, $this->alias);
            }
            if ($this->isDistinct) {
                return sprintf('%s(%s %s) as %s', $this->aggFunc, 'distinct', $this->func, $this->func);
            }
            // 非去重聚合函数直接累加分表结果
            return sprintf('sum(%s) as %s', $this->func, $this->func);
        }

        if ($this->alias) {
            return $this->alias;
        }

        return Str::after($this->name, '.');
    }

    public function getColumn(): ?string
    {
        return $this->column;
    }
    public function getName(): ?string
    {
        return $this->name;
    }

    public function getFunc(): ?string
    {
        return $this->func;
    }

    public function getAggFunc(): ?string
    {
        return $this->aggFunc;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function isDistinct(): bool
    {
        return $this->isDistinct;
    }
}
