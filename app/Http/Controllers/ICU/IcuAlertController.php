<?php

namespace App\Http\Controllers\ICU;

use App\Http\Controllers\Controller;
use App\Models\Icu\IcuAdmission;
use App\Models\Icu\IcuAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IcuAlertController extends Controller
{
    public function index($admissionId)
    {
        $admission = IcuAdmission::findOrFail($admissionId);

        $alerts = IcuAlert::where('icu_admission_id', $admission->id)
            ->orderByRaw("FIELD(status,'Active','Acknowledged','Closed')")
            ->orderByRaw("FIELD(severity,'Critical','Warning','Info')")
            ->orderByDesc('id')
            ->get();

        return view('icu.alerts.index', compact('admission', 'alerts'));
    }

    public function acknowledge(Request $request, $admissionId, $alertId)
    {
        DB::transaction(function () use ($admissionId, $alertId) {
            $alert = IcuAlert::lockForUpdate()->findOrFail($alertId);
            if ($alert->icu_admission_id != $admissionId) {
                throw new \RuntimeException('Alert does not belong to this admission.');
            }
            if ($alert->status === 'Closed') {
                throw new \RuntimeException('Alert already closed.');
            }

            $alert->update([
                'status'          => 'Acknowledged',
                'acknowledged_by' => auth()->id(),
                'acknowledged_at' => now(),
            ]);
        });

        return back()->with('success', 'Alert acknowledged.');
    }

    public function close(Request $request, $admissionId, $alertId)
    {
        $request->validate([
            'action_taken' => ['required', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($request, $admissionId, $alertId) {
            $alert = IcuAlert::lockForUpdate()->findOrFail($alertId);
            if ($alert->icu_admission_id != $admissionId) {
                throw new \RuntimeException('Alert does not belong to this admission.');
            }

            $alert->update([
                'action_taken' => $request->action_taken,
                'status'       => 'Closed',
                'closed_by'    => auth()->id(),
                'closed_at'    => now(),
            ]);
        });

        return back()->with('success', 'Alert closed.');
    }
}
