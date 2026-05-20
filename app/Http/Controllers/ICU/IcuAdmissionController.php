<?php
namespace App\Http\Controllers\ICU;

use App\Http\Controllers\Controller;
use App\Http\Requests\IcuAdmissionRequest;
use App\Models\Bed;
use App\Models\CaseReference;
use App\Models\Doctor;
use App\Models\Icu\IcuAdmission;
use App\Models\Icu\IcuAdmissionOverride;
use App\Models\Icu\IcuInfectionControlOverride;
use App\Models\IpdPatient;
use App\Models\IpdPatientBed;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IcuAdmissionController extends Controller
{
    /**
     * List ICU admissions, with optional ICU-type / status filters.
     */
    public function index(Request $request)
    {
        $query = IcuAdmission::with(['patient', 'bed.bedType', 'referringDoctor'])
            ->latest('id');

        if ($request->filled('icu_type')) {
            $query->where('icu_type', $request->icu_type);
        }
        // if ($request->filled('status')) {
        //     $query->where('status', $request->status);
        // }

        $admissions = $query->get();

        return view('icu.admissions.index', compact('admissions'));
    }

    /**
     * Show admission form. ICU type drives bed filtering.
     */
    public function create(Request $request)
    {
        $icuType = $request->input('icu_type');

        $patients = Patient::select('id', 'patient_name', 'mrn', 'mobileno', 'gender')->get();
        $doctors  = Doctor::select('id', 'name')->get();

        $beds = $this->availableIcuBedsQuery($icuType)->get();

        return view('icu.admissions.create', compact('patients', 'doctors', 'beds', 'icuType'));
    }

    /**
     * Store a new ICU admission. Performs resource validation and supports
     * an emergency override path when resources are unavailable.
     */
    public function store(IcuAdmissionRequest $request)
    {
        // dd($request->all());
        $data = $request->validated();

        DB::beginTransaction();
        try {
            // 1) Resource validation (skipped when override is used)
            $useOverride = (bool) ($data['override'] ?? false);

            $bedId = $data['bed_id'] ?? null;
            if (! $useOverride) {
                $this->validateResources($data, $bedId);
            } else {
                // Override path: temporary bed (if any) is what we record
                $bedId = $data['override_temporary_bed_id'] ?? $bedId;
            }

            // 2) Generate ICU case ID inside the transaction (locks tail row)
            $icuCaseId = IcuAdmission::generateCaseId(
                $data['icu_type'],
                new \DateTimeImmutable($data['admission_time'])
            );

            // 3) Cross-module case_id: reuse if source is Ipd, else create new
            $caseId = $this->resolveCaseId($data);

            // 4) Create the ICU admission row
            $admission = IcuAdmission::create([
                'icu_case_id'         => $icuCaseId,
                'case_id'             => $caseId,
                'patient_id'          => $data['patient_id'],
                'source_type'         => $data['source_type'],
                'source_id'           => $data['source_id'] ?? null,
                'icu_type'            => $data['icu_type'],
                'admission_type'      => $data['admission_type'] ?? 'Emergency',
                'admission_diagnosis' => $data['admission_diagnosis'],
                'referring_doctor_id' => $data['referring_doctor_id'],
                'isolation_type'      => $data['isolation_type'],
                'ventilator_required' => $data['ventilator_required'] ?? false,
                'monitor_required'    => $data['monitor_required'] ?? true,
                'bed_id'              => $bedId,
                'admission_time'      => $data['admission_time'],
                'status'              => $useOverride ? 'Admitted' : 'Admitted',
                'remarks'             => $data['remarks'] ?? null,
                'created_by'          => auth()->id(),
                'approved_by'         => $useOverride ? ($data['override_approved_by'] ?? null) : null,
            ]);

            // 5) Override audit row
            if ($useOverride) {
                IcuAdmissionOverride::create([
                    'icu_admission_id' => $admission->id,
                    'resource_issue'   => $data['override_resource_issue'],
                    'override_reason'  => $data['override_reason'],
                    'approved_by'      => $data['override_approved_by'],
                    'temporary_bed_id' => $data['override_temporary_bed_id'] ?? null,
                    'override_time'    => now(),
                    'created_by'       => auth()->id(),
                ]);

                // Additional infection-control override audit when the issue is isolation
                if (
                    ($data['override_resource_issue'] ?? null) === 'NoIsolationBed'
                    && ($data['isolation_type'] ?? 'None') !== 'None'
                    && $bedId
                ) {
                    IcuInfectionControlOverride::create([
                        'icu_admission_id'        => $admission->id,
                        'icu_case_id'             => $admission->icu_case_id,
                        'required_isolation_type' => $data['isolation_type'],
                        'assigned_bed_id'         => $bedId,
                        'override_reason'         => $data['override_reason'],
                        'approved_by'             => $data['override_approved_by'],
                        'override_time'           => now(),
                        'created_by'              => auth()->id(),
                    ]);
                }
            }

            // 6) Reserve bed and tag Ipd allocation (if any) as ICU-linked
            if ($bedId) {
                Bed::where('id', $bedId)->update(['is_reserved' => true]);
            }

            // 7) If source is Ipd, mirror an IpdPatientBed allocation tagged 'icu'
            //    so the existing Ipd timeline still tells the full story.
            if (strcasecmp((string) $data['source_type'], 'Ipd') === 0 && ! empty($data['source_id']) && $bedId) {
                $this->mirrorIpdAllocation($data['source_id'], $caseId, $bedId, $data['admission_time'], $data['remarks'] ?? null);
            }

            // 8) If source is DIRECT, also create an Ipd patient row (and mirror
            //    the bed allocation as 'icu') so the patient is visible in the
            //    Ipd module and the unified timeline shows the ICU stay.
            if (strcasecmp((string) $data['source_type'], 'DIRECT') === 0) {
                $referringDoctor = Doctor::find($data['referring_doctor_id']);

                $ipd = IpdPatient::create([
                    'case_id'         => $caseId,
                    'patient_id'      => $data['patient_id'],
                    'doctor_id'       => $data['referring_doctor_id'],
                    'department_id'   => optional($referringDoctor)->department_id,
                    'admission_date'  => $data['admission_time'],
                    'admission_type'  => $data['admission_type'] ?? 'Emergency',
                    'patient_history' => $data['admission_diagnosis'] ?? null,
                    'remarks'         => $data['remarks'] ?? null,
                    'status'          => 'Admitted',
                ]);

                $admission->update(['source_id' => $ipd->id]);

                if ($bedId) {
                    IpdPatientBed::create([
                        'case_id'         => $caseId,
                        'ipd_patient_id'  => $ipd->id,
                        'bed_id'          => $bedId,
                        'allocation_type' => 'icu',
                        'from'            => $data['admission_time'],
                        'status'          => 'Admitted',
                        'remarks'         => $data['remarks'] ?? null,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('icu.admissions.show', $admission->id)
                ->with('success', "ICU admission saved. Case ID: {$icuCaseId}");
        } catch (\Throwable $e) {
            // dd($e->getMessage());
            DB::rollBack();
            Log::error('ICU admission failed', ['message' => $e->getMessage()]);
            return back()->withInput()->with('error', 'ICU admission failed: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $admission = IcuAdmission::with([
            'patient',
            'bed.bedType',
            'referringDoctor',
            'overrides.temporaryBed',
            'dischargeSummary',
            'mortalityAudit',
        ])->findOrFail($id);

        $unitKey = $admission->unitKey();
        $admission->load([
            'pathologyOrders' => fn($q) => $q->where('source', $unitKey),
            'pathologyOrders.requests.labInvestigation',
            'pathologyOrders.requests.labInvestigationCategory',
            'pathologyOrders.doctor',
            'radiologyOrders' => fn($q) => $q->where('source', $unitKey),
            'radiologyOrders.requests.labInvestigation',
            'radiologyOrders.requests.labInvestigationCategory',
            'radiologyOrders.doctor',
            'alerts' => fn($q) => $q->latest('id'),
            'nursingNotes' => fn($q) => $q->latest('observation_time')->latest('id'),
            'doctorOrders' => fn($q) => $q->latest('id'),
            'doctorOrders.doctor',
            'activeEquipmentUsage.equipment',
            'activeInfections',
            'transfers' => fn($q) => $q->latest('transfer_time')->latest('id'),
            'transfers.fromBed',
            'transfers.toBed',
            'intakeOutputEntries' => fn($q) => $q->latest('entry_time')->latest('id'),
            'emergencyEvents' => fn($q) => $q->latest('id'),
        ]);

        $latestVital = $admission->vitalLogs()
            ->latest('recorded_at')
            ->latest('id')
            ->first();

        $vitalCount = $admission->vitalLogs()->count();

        $io24Start = now()->subDay();
        $intake24h = (int) $admission->intakeOutputEntries()
            ->where('entry_type', 'Intake')
            ->where('entry_time', '>=', $io24Start)
            ->sum('quantity_ml');
        $output24h = (int) $admission->intakeOutputEntries()
            ->where('entry_type', 'Output')
            ->where('entry_time', '>=', $io24Start)
            ->sum('quantity_ml');

        return view('icu.admissions.show', compact(
            'admission',
            'latestVital',
            'vitalCount',
            'intake24h',
            'output24h'
        ));
    }

    /**
     * Bed availability lookup endpoint for the form (AJAX-friendly).
     * Returns ICU beds matching the given icu_type and (optional) isolation_type.
     */
    public function availableBeds(Request $request)
    {
        $icuType       = $request->input('icu_type');
        $isolationType = $request->input('isolation_type');
        $needVent      = filter_var($request->input('ventilator_required'), FILTER_VALIDATE_BOOLEAN);

        $beds = $this->availableIcuBedsQuery($icuType, $isolationType, $needVent)->get();

        return response()->json([
            'beds' => $beds->map(fn($b) => [
                'id'           => $b->id,
                'name'         => $b->name,
                'rent'         => $b->rent,
                'bed_type'     => optional($b->bedType)->name,
                'icu_type'     => optional($b->bedType)->icu_type,
                'has_vent'     => (bool) optional($b->bedType)->has_ventilator_support,
                'has_monitor'  => (bool) optional($b->bedType)->has_monitor_support,
                'is_isolation' => (bool) optional($b->bedType)->is_isolation_bed,
            ]),
        ]);
    }

    // ---------- Helpers ----------

    /**
     * Build a query for currently available ICU beds, optionally filtered
     * by ICU type, isolation type, and ventilator capability.
     */
    protected function availableIcuBedsQuery(?string $icuType = null, ?string $isolationType = null, bool $needVent = false)
    {
        $q = Bed::with('bedType')
            ->where('is_reserved', false)
            ->whereHas('bedType', function ($bt) use ($icuType, $isolationType, $needVent) {
                $bt->where('is_icu', true);
                if ($icuType) {
                    $bt->where('icu_type', $icuType);
                }
                if ($isolationType && $isolationType !== 'None') {
                    $bt->where('is_isolation_bed', true)
                        ->where(function ($q2) use ($isolationType) {
                            $q2->whereNull('allowed_isolation_type')
                                ->orWhere('allowed_isolation_type', $isolationType);
                        });
                }
                if ($needVent) {
                    $bt->where('has_ventilator_support', true);
                }
            });

        return $q;
    }

    protected function validateResources(array $data, ?int $bedId): void
    {
        if (! $bedId) {
            throw new \RuntimeException('No ICU bed selected. Use Emergency Override if no bed is available.');
        }

        $bed = Bed::with('bedType')->findOrFail($bedId);

        if ($bed->is_reserved) {
            throw new \RuntimeException('Selected ICU bed is already occupied.');
        }
        if (! optional($bed->bedType)->is_icu) {
            throw new \RuntimeException('Selected bed is not an ICU bed.');
        }
        if (optional($bed->bedType)->icu_type !== $data['icu_type']) {
            throw new \RuntimeException('Bed ICU type does not match the requested ICU type.');
        }
        if (! empty($data['ventilator_required']) && ! optional($bed->bedType)->has_ventilator_support) {
            throw new \RuntimeException('Patient needs ventilator but bed does not support ventilator. Use Emergency Override if necessary.');
        }
        $isolation = $data['isolation_type'] ?? 'None';
        if ($isolation !== 'None') {
            $isIso      = (bool) optional($bed->bedType)->is_isolation_bed;
            $allowedFor = optional($bed->bedType)->allowed_isolation_type;
            if (! $isIso) {
                throw new \RuntimeException('Patient requires isolation but bed is not an isolation bed. Use Emergency Override if necessary.');
            }
            if ($allowedFor && $allowedFor !== $isolation) {
                throw new \RuntimeException("Bed accepts {$allowedFor} isolation only; patient needs {$isolation}.");
            }
        }
    }

    protected function resolveCaseId(array $data): ?int
    {
        if (strcasecmp((string) $data['source_type'], 'Ipd') === 0 && ! empty($data['source_id'])) {
            $ipd = IpdPatient::find($data['source_id']);
            if ($ipd && $ipd->case_id) {
                return $ipd->case_id;
            }
        }
        // Fallback: create a fresh case reference
        return CaseReference::create()->id;
    }

    protected function mirrorIpdAllocation(int $ipdPatientId, ?int $caseId, int $bedId, string $from, ?string $remarks): void
    {
        $ipd = IpdPatient::find($ipdPatientId);
        if (! $ipd) {
            return;
        }

        // Close any open Ipd allocation
        $current = IpdPatientBed::where('ipd_patient_id', $ipdPatientId)
            ->whereNull('to')
            ->orderByDesc('id')
            ->first();

        if ($current) {
            $current->update(['to' => $from, 'status' => 'TRANSFERRED']);
            Bed::where('id', $current->bed_id)->update(['is_reserved' => false]);
        }

        IpdPatientBed::create([
            'case_id'         => $caseId ?? $ipd->case_id,
            'ipd_patient_id'  => $ipdPatientId,
            'bed_id'          => $bedId,
            'allocation_type' => 'icu',
            'from'            => $from,
            'status'          => 'Admitted',
            'remarks'         => $remarks,
        ]);
    }
}
