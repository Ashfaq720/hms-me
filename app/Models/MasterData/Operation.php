<?php
namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class Operation extends Model
{
    protected $fillable = ['name', 'charge'];

    public function procedures()
    {
        return $this->hasMany(OperationProcedure::class);
    }
}
