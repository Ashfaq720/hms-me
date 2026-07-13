<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class VoucherController extends Controller
{
    public function index()
    {
        // Vouchers are gl_journals where source = 'voucher' or 'manual'
        $vouchers = DB::table('gl_journals')
            ->whereIn('source', ['voucher', 'manual', 'cash_receipt', 'cash_payment', 'bank_receipt', 'bank_payment'])
            ->orderByDesc('id')->paginate(50);
        return view('accounting.voucher.index', compact('vouchers'));
    }

    public function create()
    {
        $accounts = DB::table('chart_of_accounts')->where('is_active', 1)->orderBy('code')->get();
        return view('accounting.voucher.create', compact('accounts'));
    }
}
