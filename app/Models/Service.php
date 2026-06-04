<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    const STATUS_ALL = 2;

    const ACTIVE = 1;

    const INACTIVE = 0;

    const STATUS_ARR = [
        self::STATUS_ALL => 'All',
        self::ACTIVE => 'Active',
        self::INACTIVE => 'Deactive',
    ];

    const FILTER_STATUS_ARRAY = [
        0 => 'All',
        1 => 'Active',
        2 => 'Deactive',
    ];

   public static $rules = [
    'name' => 'required|string|max:255|unique:services,name',
    'quantity' => 'required|numeric|min:0',
    'rate' => 'required|numeric|min:0',
];


    public $fillable = [
        'name',
        'description',
        'quantity',
        'rate',
        'status',
    ];

    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'description' => 'string',
        'quantity' => 'integer',
        'rate' => 'double',
        'status' => 'integer',
    ];
}
