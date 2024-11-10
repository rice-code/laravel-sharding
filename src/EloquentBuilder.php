<?php

namespace Rice\LSharding;

use Illuminate\Database\Eloquent\Builder;

class EloquentBuilder extends Builder
{
    public function getModels($columns = ['*'])
    {
        if ($this->model instanceof Sharding) {
            $queryList = [];
            foreach ($this->model->getTables() as $table) {
                $query = clone $this->query;
                $query->from($table);
                QueryManager::replaceColumns($query, $this->model->getOriginalTable(), $table);
                QueryManager::replaceWheres($query, $this->model->getOriginalTable(), $table);
                $queryList = [...$queryList, ...$query->get($columns)->all()];
            }

            return $this->model->hydrate($queryList)->all();
        }

        return parent::getModels($columns);
    }
}
