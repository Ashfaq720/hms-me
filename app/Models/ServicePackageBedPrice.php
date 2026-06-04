<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicePackageBedPrice extends Model
{
    protected $table = 'service_package_bed_prices';

    protected $fillable = ['service_package_id', 'bed_type_id', 'price'];

    protected $casts = ['price' => 'decimal:2'];

    public function package()
    {
        return $this->belongsTo(ServicePackage::class, 'service_package_id');
    }

    public function bedType()
    {
        return $this->belongsTo(BedType::class);
    }
}
