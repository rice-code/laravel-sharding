<?php

namespace Rice\LSharding;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Model;
use Rice\LSharding\Traits\ReplaceTrait;
use Illuminate\Database\ConnectionInterface;

class ShardingBuilder
{
    use ReplaceTrait;

    protected Model $model;
    protected Builder $query;
    protected array $queries;
    /**
     * @var Column[]
     */
    protected array $fields  = [];

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
            $this->replaceColumns($query, $this->model->getOriginalTable(), $table);
            $this->replaceWheres($query, $this->model->getOriginalTable(), $table);
        }

        return $this->model->hydrate($this->multiTableQuery($this->model->getConnection(), $unionQueries))->all();
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
        foreach ($this->query->columns as $column) {
            $this->fields[strtolower($column)] = new Column($column);
        }
        dump($this->fields);
    }

    public function multiTableQuery(ConnectionInterface $connection, $unionAllQuery): array
    {
        $query = (new Builder($connection));

        return $query->fromSub($unionAllQuery, 't')
            ->selectRaw($this->getSelectRaw())
            ->when($this->query->groups, function ($query) {
                return $query->groupBy(implode(',', $this->query->groups));
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

        return implode(',', $rawArr);
    }
}
