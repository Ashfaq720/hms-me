<?php

namespace App\Http\Controllers\OT;

use App\Http\Controllers\Controller;
use App\Models\Ot\OtConsumableUsage;
use App\Services\Ot\OtInventoryService;
use Illuminate\Http\Request;

class OtInventoryController extends OtBaseController
{
    public function __construct(protected OtInventoryService $inventory) {}

    public function index(Request $request)
    {
        $this->gate('ot_inventory_access');
        $usages = OtConsumableUsage::with(['schedule.surgeryRequest.patient', 'consumable'])
            ->where('inventory_deducted', false)
            ->latest()
            ->paginate(30);

        return view('ot.inventory.index', compact('usages'));
    }

    public function deduct(Request $request, $usageId)
    {
        $usage = OtConsumableUsage::findOrFail($usageId);

        if ($usage->inventory_deducted) {
            return back()->with('error', 'Already deducted from inventory.');
        }

        try {
            $this->inventory->deduct($usage);
        } catch (\Throwable $e) {
            return back()->with('error', 'Stock deduction failed: ' . $e->getMessage());
        }

        return back()->with('success', 'Inventory deducted (Pharmacy batches updated when linked).');
    }
}
