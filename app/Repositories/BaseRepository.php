<?php

namespace App\Repositories;

use App\Repositories\Interfaces\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected $model;
    protected $query;
    
    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->resetQuery();
    }
    
    public function all()
    {
        return $this->query->get();
    }
    
    public function find(int $id)
    {
        return $this->query->find($id);
    }
    
    public function create(array $data)
    {
        return $this->model->create($data);
    }
    
    public function update(int $id, array $data)
    {
        $record = $this->model->findOrFail($id);
        $record->update($data);
        return $record->refresh();
    }
    
    public function delete(int $id): bool
    {
        $record = $this->model->findOrFail($id);
        return $record->delete();
    }
    
    public function findWhere(array $criteria)
    {
        $query = $this->query;
        
        foreach ($criteria as $field => $value) {
            if (is_array($value)) {
                [$operator, $val] = $value;
                $query = $query->where($field, $operator, $val);
            } else {
                $query = $query->where($field, $value);
            }
        }
        
        return $query->get();
    }
    
    public function findWhereFirst(array $criteria)
    {
        $query = $this->query;
        
        foreach ($criteria as $field => $value) {
            if (is_array($value)) {
                [$operator, $val] = $value;
                $query = $query->where($field, $operator, $val);
            } else {
                $query = $query->where($field, $value);
            }
        }
        
        return $query->first();
    }
    
    public function paginate(int $perPage = 15, array $criteria = [])
    {
        $query = $this->query;
        
        foreach ($criteria as $field => $value) {
            if (is_array($value)) {
                [$operator, $val] = $value;
                $query = $query->where($field, $operator, $val);
            } else {
                $query = $query->where($field, $value);
            }
        }
        
        return $query->paginate($perPage);
    }
    
    public function with(array $relations)
    {
        $this->query = $this->query->with($relations);
        return $this;
    }
    
    public function orderBy(string $column, string $direction = 'asc')
    {
        $this->query = $this->query->orderBy($column, $direction);
        return $this;
    }
    
    public function where(string $column, $operator = null, $value = null)
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }
        
        $this->query = $this->query->where($column, $operator, $value);
        return $this;
    }
    
    public function whereIn(string $column, array $values)
    {
        $this->query = $this->query->whereIn($column, $values);
        return $this;
    }
    
    public function whereDate(string $column, string $operator, string $date = null)
    {
        if ($date === null) {
            $date = $operator;
            $operator = '=';
        }
        
        $this->query = $this->query->whereDate($column, $operator, $date);
        return $this;
    }
    
    public function whereBetween(string $column, array $values)
    {
        $this->query = $this->query->whereBetween($column, $values);
        return $this;
    }
    
    public function orWhere(string $column, $operator = null, $value = null)
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }
        
        $this->query = $this->query->orWhere($column, $operator, $value);
        return $this;
    }
    
    public function latest(string $column = 'created_at')
    {
        $this->query = $this->query->latest($column);
        return $this;
    }
    
    public function oldest(string $column = 'created_at')
    {
        $this->query = $this->query->oldest($column);
        return $this;
    }
    
    public function count()
    {
        return $this->query->count();
    }
    
    public function sum(string $column)
    {
        return $this->query->sum($column);
    }
    
    public function avg(string $column)
    {
        return $this->query->avg($column);
    }
    
    public function max(string $column)
    {
        return $this->query->max($column);
    }
    
    public function min(string $column)
    {
        return $this->query->min($column);
    }
    
    public function exists(): bool
    {
        return $this->query->exists();
    }
    
    public function first()
    {
        return $this->query->first();
    }
    
    public function get()
    {
        $result = $this->query->get();
        $this->resetQuery();
        return $result;
    }
    
    protected function resetQuery()
    {
        $this->query = $this->model->newQuery();
    }
    
    public function getModel()
    {
        return $this->model;
    }
    
    public function getQuery(): Builder
    {
        return $this->query;
    }
}
