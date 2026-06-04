<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class GlPosting extends Model
{
    protected $fillable = [
        'gl_journal_id', 'chart_of_account_id', 'cost_center_id',
        'debit', 'credit', 'description',
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    public function journal()
    {
        return $this->belongsTo(GlJournal::class, 'gl_journal_id');
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }
}
