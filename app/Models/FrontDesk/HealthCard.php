<?php

namespace App\Models\FrontDesk;

use Illuminate\Database\Eloquent\Model;

class HealthCard extends Model
{
    protected $table = 'health_cards';

    // health_cards has 3 columns (id + 1-2 fields). Listing what exists.
    protected $fillable = ['patient_id', 'card_no'];
}
