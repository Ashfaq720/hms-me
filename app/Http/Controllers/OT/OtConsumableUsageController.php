<?php

namespace App\Http\Controllers\OT;

use App\Http\Controllers\Controller;
use App\Models\Ot\OtAuditLog;
use App\Models\Ot\OtConsumable;
use App\Models\Ot\OtConsumableUsage;
use App\Models\Ot\OtSurgerySchedule;
use Illuminate\Http\Request;

class OtConsumableUsageController extends OtBaseController
{
    public function index(Request $request)
    {
        $this->gate('ot_consumables_access');
        $usages = OtConsumableUsage::with(['schedule.surgeryRequest.patient', 'consumable'])
            ->latest()
            ->paginate(30);

        return view('ot.consumables.index', compact('usages'));
    }

    public function show($scheduleId)
    {
        $schedule = OtSurgerySchedule::with(['surgeryRequest.patient', 'consumableUsages.consumable'])
            ->findOrFail($scheduleId);
        $consumables = OtConsumable::where('is_active', true)->orderBy('name')->get();

        return view('ot.consumables.show', compact('schedule', 'consumables'));
    }

    public function store(Request $request, $scheduleId)
    {
        $data = $request->validate([
            'ot_consumable_id' => 'nullable|exists:ot_consumables,id',
            'item_name' => 'required|string|max:255',
            'item_code' => 'nullable|string|max:50',
            'type' => 'required|in:consumable,implant,instrument,medicine',
            'quantity' => 'required|numeric|min:0.01',
            'unit' => 'nullable|string|max:20',
            'rate' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $data['surgery_schedule_id'] = $scheduleId;
        $data['amount'] = $data['quantity'] * $data['rate'];
        $data['used_at'] = now();
        $data['recorded_by'] = auth()->id();

        $usage = OtConsumableUsage::create($data);

        OtAuditLog::record('consumable_usage', $usage->id, 'recorded', null, null, null, $data);

        return back()->with('success', 'Usage recorded. (Inventory deduction + billing pending — handled when posted.)');
    }

    public function destroy($id)
    {
        $usage = OtConsumableUsage::findOrFail($id);

        if ($usage->is_billed || $usage->inventory_deducted) {
            return back()->with('error', 'Cannot delete a usage entry that has been billed or inventory-deducted.');
        }

        $usage->delete();
        OtAuditLog::record('consumable_usage', $usage->id, 'removed');

        return back()->with('success', 'Usage entry removed.');
    }
}
