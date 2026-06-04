<?php

namespace App\View\Components\Ipd;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\View\Component;

/**
 * Unified Lab Orders panel — replaces separate Pathology + Radiology tabs.
 * Pulls from lab_investigation_order and groups by type for clarity.
 */
class LabOrders extends Component
{
    public $iPDPatient;
    public $orders;
    public $byType;

    public function __construct($iPDPatient)
    {
        $this->iPDPatient = $iPDPatient;
        $this->orders = DB::table('lab_investigation_order as o')
            ->leftJoin('doctors as d', 'd.id', '=', 'o.doctor_id')
            ->where('o.ipd_id', $iPDPatient->id)
            ->select('o.*', 'd.name as doctor_name')
            ->orderByDesc('o.datetime')->get();
        $this->byType = $this->orders->groupBy('type');
    }

    public function render(): View | Closure | string
    {
        return view('components.ipd.lab-orders');
    }
}
