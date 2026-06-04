<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'supplier_name',
        'contact_supplier',
        'contact_person_name',
        'contact_person_telephone',
        'drug_license_number',
        'address',
        'status',
    ];
}
