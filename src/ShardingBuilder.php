<?php

namespace Rice\LSharding;

use Illuminate\Support\Str;
use Rice\LSharding\Traits\ColumnTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder as QueryBuilder;

class ShardingBuilder
{
    use ColumnTrait;

    protected Model $model;
    protected QueryBuilder $query;
    protected array $queries;
    /**
     * @var Column[]
     */
    protected array $fields  = [];
    protected array $alias   = [];

    public function __construct(Builder $builder)
    {
        $this->model = $builder->getModel();
        $this->query = $builder->getQuery();
        if ($this->model instanceof Sharding) {
            $this->model->algorithm->builder = $builder;
        }
        $this->fieldFilter();
    }

    public function getModels()
    {
        $unionQueries = null;

        foreach ($this->model->getTables() as $table) {
            $query = clone $this->query;
            if (is_null($unionQueries)) {
                $unionQueries = $query;
            } else {
                $unionQueries->unionAll($query);
            }

            $originalTable = $this->model->getOriginalTable();

            $query->from($table);

            if ($table === $originalTable) {
                continue;
            }

            $this->addColumns($query, $table);
            $this->replaceColumns($query, $originalTable, $table);
            $this->replaceWheres($query, $originalTable, $table);
        }

        return $this->model->hydrate($this->subQuery($this->model->getConnection(), $unionQueries))->all();
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    protected function fieldFilter(): void
    {
        foreach ($this->query->columns ?? [] as $column) {
            // 细化处理 raw 查询字段
            foreach (explode(',', $column) as $subColumn) {
                $field                             = new Column($subColumn);
                $this->fields[$field->getColumn()] = $field;
                $this->alias[$field->getAlias()]   = $field;
            }
        }
    }

    public function subQuery(ConnectionInterface $connection, $unionAllQuery): array
    {
        $query = (new QueryBuilder($connection));

        return $query->fromSub($unionAllQuery, Str::random(6))
            ->selectRaw($this->getSelectRaw())
            ->when($this->query->orders, function ($query) {
                foreach ($this->query->orders as $order) {
                    $query->orderBy($order['column'], $order['direction']);
                }

                return $query;
            })
            ->when($this->query->groups, function ($query) {
                return $query->groupBy(...$this->query->groups);
            })
            ->get()
            ->all();
    }

    protected function getSelectRaw(): string
    {
        $rawArr = [];
        foreach ($this->fields as $field) {
            $rawArr[] = $field->getSelectColumn();
        }

        if (empty($rawArr)) {
            return '*';
        }

        return implode(',', array_unique($rawArr));
    }
}
