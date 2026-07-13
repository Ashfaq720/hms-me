<?php
namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class OperationProcedure extends Model
{
    protected $fillable = ['operation_id', 'name'];

    public function operation()
    {
        return $this->belongsTo(Operation::class);
    }
}
