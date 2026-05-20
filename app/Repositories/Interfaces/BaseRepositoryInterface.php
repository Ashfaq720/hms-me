<?php

namespace App\Repositories\Interfaces;

interface BaseRepositoryInterface
{
    public function all();
    
    public function find(int $id);
    
    public function create(array $data);
    
    public function update(int $id, array $data);
    
    public function delete(int $id): bool;
    
    public function findWhere(array $criteria);
    
    public function findWhereFirst(array $criteria);
    
    public function paginate(int $perPage = 15, array $criteria = []);
    
    public function with(array $relations);
    
    public function orderBy(string $column, string $direction = 'asc');
}
