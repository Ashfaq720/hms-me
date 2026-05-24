<?php

namespace App\Models\Charges;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Charge extends Model
{
    use HasFactory;

    protected $fillable = [
        'charge_type_id',
        'charge_category_id',
        'unite_type_id',
        'tax_category_id',
        'charge_name',
        'tax',
        'standard_charge',
        'description',
    ];

    public function chargeType()
    {
        return $this->belongsTo(ChargeType::class, 'charge_type_id');
    }

    public function chargeCategory()
    {
        return $this->belongsTo(ChargeCategory::class, 'charge_category_id');
    }

    public function uniteType()
    {
        return $this->belongsTo(UniteType::class, 'unite_type_id');
    }

    public function taxCategory()
    {
        return $this->belongsTo(TaxCategory::class, 'tax_category_id');
    }
}
