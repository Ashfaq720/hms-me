<?php
namespace App\Models\BloodBank;

use Illuminate\Database\Eloquent\Model;

class ComponentTemperatureRule extends Model
{

    protected $fillable = [
        'component_id', 'min_temp', 'max_temp',
        'monitoring_required', 'is_active', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'min_temp'          => 'decimal:2',
        'max_temp'          => 'decimal:2',
        'monitoring_required' => 'boolean',
        'is_active'           => 'boolean',
    ];

    public function component()
    {
        return $this->belongsTo(Component::class, 'component_id');
    }
}
