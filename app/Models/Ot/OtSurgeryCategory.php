<?php

namespace App\Models\Ot;

use Illuminate\Database\Eloquent\Model;

class OtSurgeryCategory extends Model
{
    protected $table = 'ot_surgery_categories';

    protected $fillable = ['name', 'code', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function surgeryTypes()
    {
        return $this->hasMany(OtSurgeryType::class, 'category_id');
    }
}
