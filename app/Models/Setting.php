<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Setting extends Model
{
    use HasFactory;

    protected $table = 'settings';

    protected $guarded = ['id'];

    protected $casts = [
        'value' => 'json',
        'is_public' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Return company info settings as an associative array keyed by trimmed key
     * (strips the 'company_' prefix where present).
     *
     * @return array
     */
    public static function getCompanyInfo(): array
    {
        $rows = static::where('group', 'company')
            ->where('is_active', true)
            ->get();

        $info = [];
        foreach ($rows as $row) {
            $key = $row->key;
            // remove company_ prefix if present
            if (str_starts_with($key, 'company_')) {
                $key = substr($key, 8);
            }
            $info[$key] = $row->value;
        }

        return $info;
    }
}
