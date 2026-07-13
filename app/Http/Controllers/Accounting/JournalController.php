<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class JournalController extends Controller
{
    public function index()
    {
        $journals = DB::table('gl_journals')
            ->leftJoin('users', 'users.id', '=', 'gl_journals.created_by')
            ->select('gl_journals.*', 'users.name as creator_name')
            ->orderByDesc('gl_journals.id')
            ->paginate(50);

        return view('accounting.journal.index', compact('journals'));
    }

    public function show($id)
    {
        $journal = DB::table('gl_journals')->where('id', $id)->first();
        abort_unless($journal, 404);
        $postings = DB::table('gl_postings')
            ->leftJoin('chart_of_accounts as coa', 'coa.id', '=', 'gl_postings.chart_of_account_id')
            ->where('gl_postings.gl_journal_id', $id)
            ->select('gl_postings.*', 'coa.name as account_name', 'coa.code as account_code')
            ->get();
        return view('accounting.journal.show', compact('journal', 'postings'));
    }
}
