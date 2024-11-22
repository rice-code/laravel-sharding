<?php

namespace Rice\LSharding;

use Illuminate\Support\Str;
use Illuminate\Database\Query\Builder;
use Rice\LSharding\Traits\ColumnTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\ConnectionInterface;

class ShardingBuilder
{
    use ColumnTrait;

    protected Model $model;
    protected Builder $query;
    protected array $queries;
    /**
     * @var Column[]
     */
    protected array $fields  = [];
    protected array $alias   = [];

    public function __construct(Model $model, Builder $query)
    {
        $this->model = $model;
        $this->query = $query;
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
            $query->from($table);
            $this->addColumns($query, $table);
            $this->replaceColumns($query, $this->model->getOriginalTable(), $table);
            $this->replaceWheres($query, $this->model->getOriginalTable(), $table);
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
            $field                             = new Column($column);
            $this->fields[$field->getColumn()] = $field;
            $this->alias[$field->getAlias()]   = $field;
        }
    }

    public function subQuery(ConnectionInterface $connection, $unionAllQuery): array
    {
        $query = (new Builder($connection));

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
