<?php

namespace App\Models\Inventory;

use App\Traits\BranchScoped;
use App\Traits\LogPreference;
use Illuminate\Database\Eloquent\Model;

class InventoryWarehouse extends Model
{
    use LogPreference, BranchScoped;

    protected string $logName = 'inventory_warehouse';

    protected $fillable = [
        'organization_id', 'branch_id', 'code', 'name', 'type',
        'location', 'manager_user_id', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function movements()
    {
        return $this->hasMany(StockMovement::class, 'warehouse_id');
    }

    public function batches()
    {
        return $this->hasMany(InventoryItemBatch::class, 'warehouse_id');
    }
}
