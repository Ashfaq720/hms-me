<?php

namespace App\Http\Controllers\ICU;

use App\Http\Controllers\Controller;
use App\Models\Icu\IcuEmergencyEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class IcuCodeBlueController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->filled('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : Carbon::now()->subDays(30)->startOfDay();
        $to = $request->filled('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : Carbon::now()->endOfDay();

        $status   = $request->input('status');
        $search   = trim((string) $request->input('q', ''));
        $icuType  = strtoupper((string) $request->input('icu_type', 'ICU')) ?: 'ICU';
        $icuType  = in_array($icuType, ['ICU', 'CCU', 'NICU', 'PICU'], true) ? $icuType : 'ICU';

        $query = IcuEmergencyEvent::with([
                'patient:id,patient_name,mrn,dob,gender',
                'admission:id,icu_case_id,icu_type,bed_id,isolation_type',
                'admission.bed:id,name',
                'admission.activeInfections',
            ])
            ->whereHas('admission', fn ($q) => $q->where('icu_type', $icuType))
            ->whereBetween('activated_at', [$from, $to]);

        if ($status) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('event_no', 'like', "%{$search}%")
                  ->orWhere('icu_case_id', 'like', "%{$search}%")
                  ->orWhereHas('patient', function ($p) use ($search) {
                      $p->where('patient_name', 'like', "%{$search}%")
                        ->orWhere('mrn', 'like', "%{$search}%");
                  });
            });
        }

        $events = $query->orderByDesc('activated_at')->get();

        $rows = $events->map(function (IcuEmergencyEvent $event) {
            $admission = $event->admission;
            $patient   = $event->patient;

            $intake = 0;
            $output = 0;
            if ($admission) {
                $entries = $admission->intakeOutputEntries()
                    ->where('entry_time', '>=', $event->activated_at)
                    ->when($event->closed_at, fn ($q) => $q->where('entry_time', '<=', $event->closed_at))
                    ->get(['entry_type', 'quantity_ml']);

                $intake = (int) $entries->where('entry_type', 'Intake')->sum('quantity_ml');
                $output = (int) $entries->where('entry_type', 'Output')->sum('quantity_ml');
            }

            $infection = optional($admission?->activeInfections?->first());

            return (object) [
                'id'              => $event->id,
                'admission_id'    => $event->icu_admission_id,
                'event_no'        => $event->event_no,
                'icu_case_id'     => $event->icu_case_id,
                'patient_name'    => $patient?->patient_name ?? '-',
                'mrn'             => $patient?->mrn,
                'age'             => $patient?->dob ? calculateAgeFromDob($patient->dob) : null,
                'gender'          => $patient?->gender,
                'bed_name'        => $event?->bed?->name,
                'isolation_type'  => $admission?->isolation_type,
                'infection_name'  => $infection->infection_name ?? null,
                'infection_state' => $infection->infection_status ?? null,
                'activated_at'    => $event->activated_at,
                'closed_at'       => $event->closed_at,
                'status'          => $event->status,
                'event_type'      => $event->event_type,
                'intake_ml'       => $intake,
                'output_ml'       => $output,
                'balance_ml'      => $intake - $output,
            ];
        });

        $stats = [
            'total'      => $rows->count(),
            'open'       => $rows->whereNotIn('status', ['Closed'])->count(),
            'stabilized' => $rows->where('status', 'Stabilized')->count(),
            'closed'     => $rows->where('status', 'Closed')->count(),
        ];

        return view('icu.code-blue.index', [
            'rows'    => $rows,
            'from'    => $from,
            'to'      => $to,
            'status'  => $status,
            'search'  => $search,
            'stats'   => $stats,
            'icuType' => $icuType,
        ]);
    }
}
