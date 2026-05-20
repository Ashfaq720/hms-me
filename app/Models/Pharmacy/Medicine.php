<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'medicine_name',
        'medicine_category_id',
        'company_id',
        'medical_group_id',
        'medicine_unit_id',
        'medicine_composition',
        'min_level',
        'reorder_level',
        'tax',
        'box_packing',
        'vat_ac',
        'rack_number',
        'note',
        'photo',
        'available_qty',
        'status',
    ];

    public function category()
    {
        return $this->belongsTo(MedicineCategory::class, 'medicine_category_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function medicalGroup()
    {
        return $this->belongsTo(MedicalGroup::class, 'medical_group_id');
    }

    public function unit()
    {
        return $this->belongsTo(MedicineUnit::class, 'medicine_unit_id');
    }

    public function batches()
    {
        return $this->hasMany(MedicineBatch::class);
    }
}
