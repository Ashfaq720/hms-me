<?php

namespace App\Models\Accounting;

use App\Traits\BranchScoped;
use Illuminate\Database\Eloquent\Model;

class GlJournal extends Model
{
    use BranchScoped;

    protected $fillable = [
        'organization_id', 'branch_id', 'journal_no',
        'posting_date', 'source', 'reference_type', 'reference_id',
        'memo', 'status', 'created_by', 'reversed_by',
    ];

    protected $casts = ['posting_date' => 'date'];

    public function postings()
    {
        return $this->hasMany(GlPosting::class);
    }
}
