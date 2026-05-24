<?php

namespace App\Http\Controllers\NICU;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use App\Models\BedType;
use App\Models\CaseReference;
use App\Models\IpdPatient;
use App\Models\NicuAdmission;
use App\Models\Ot\OtSurgerySchedule;
use App\Models\Patient;
use App\Models\ServicePackage;
use App\Services\PackageBillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * NICU admission flow — Phase A.
 *
 * Owns the lifecycle: create-from-OT (auto generates baby Patient +
 * case_reference + NICU admission), create-from-IPD (mother already
 * admitted), index/show, discharge. Future Phase B will add vitals /
 * feeding / growth / dosing controllers alongside this one.
 */
class NicuAdmissionController extends Controller
{
    public function index(Request $request)
    {
        $this->gate('nicu_access');

        $q = NicuAdmission::with([
            'baby:id,patient_name,gender,parent_patient_id',
            'mother:id,patient_name',
            'bed.bedType',
            'servicePackage:id,code,name',
        ]);

        if (in_array($request->get('status'), NicuAdmission::STATUSES, true)) {
            $q->where('status', $request->get('status'));
        }
        if ($s = trim((string) $request->get('search'))) {
            $q->where(function ($x) use ($s) {
                $x->where('admission_no', 'like', "%{$s}%")
                  ->orWhereHas('baby',   fn ($y) => $y->where('patient_name', 'like', "%{$s}%"))
                  ->orWhereHas('mother', fn ($y) => $y->where('patient_name', 'like', "%{$s}%"));
            });
        }

        $admissions = $q->latest('id')->paginate(25)->appends($request->query());

        return view('nicu.index', compact('admissions'));
    }

    public function show(NicuAdmission $nicuAdmission)
    {
        $this->gate('nicu_access');
        $nicuAdmission->load([
            'baby', 'mother', 'caseReference.parentCase',
            'bed.bedType', 'bedType',
            'admittedBy', 'dischargedBy', 'servicePackage',
        ]);
        $source = $nicuAdmission->resolveSource();

        return view('nicu.show', compact('nicuAdmission', 'source'));
    }

    /**
     * Create a NICU admission for a newborn delivered during an OT
     * surgery (typically a C-Section). Auto-generates the baby patient
     * record + linked case file + applies risk flags + allocates the
     * suggested bed.
     */
    public function createFromOt(Request $request, $scheduleId)
    {
        $this->gate('nicu_create');

        $schedule = OtSurgerySchedule::with('surgeryRequest.patient')->findOrFail($scheduleId);
        $mother   = optional($schedule->surgeryRequest)->patient;

        $data = $this->validateBirth($request);

        $admission = DB::transaction(function () use ($data, $schedule, $mother) {
            return $this->createAdmission($data, [
                'mother'      => $mother,
                'source_type' => NicuAdmission::SOURCE_OT,
                'source_id'   => $schedule->id,
            ]);
        });

        return redirect()
            ->route('nicu.admissions.show', $admission)
            ->with('success', "NICU admission {$admission->admission_no} created for newborn.");
    }

    /**
     * Create a NICU admission from an IPD admission (vaginal delivery
     * or post-delivery transfer). Mother is the IPD patient.
     */
    public function createFromIpd(Request $request, $ipdId)
    {
        $this->gate('nicu_create');

        $ipd    = IpdPatient::with('patient')->findOrFail($ipdId);
        $mother = $ipd->patient;

        $data = $this->validateBirth($request);

        $admission = DB::transaction(function () use ($data, $ipd, $mother) {
            return $this->createAdmission($data, [
                'mother'      => $mother,
                'source_type' => NicuAdmission::SOURCE_IPD,
                'source_id'   => $ipd->id,
            ]);
        });

        return redirect()
            ->route('nicu.admissions.show', $admission)
            ->with('success', "NICU admission {$admission->admission_no} created for newborn.");
    }

    public function discharge(Request $request, NicuAdmission $nicuAdmission)
    {
        $this->gate('nicu_discharge');

        $data = $request->validate([
            'discharge_summary' => ['required', 'string', 'max:5000'],
        ]);

        $nicuAdmission->update([
            'status'            => NicuAdmission::STATUS_DISCHARGED,
            'discharge_summary' => $data['discharge_summary'],
            'discharged_at'     => now(),
            'discharged_by'     => auth()->id(),
        ]);

        // Release the bed
        if ($nicuAdmission->bed_id) {
            Bed::where('id', $nicuAdmission->bed_id)->update(['is_reserved' => false]);
        }

        return back()->with('success', 'NICU admission discharged.');
    }

    /* ───────────── helpers ───────────── */

    protected function validateBirth(Request $request): array
    {
        return $request->validate([
            'baby_name'             => ['nullable', 'string', 'max:200'],
            'gender'                => ['required', 'in:Male,Female,Other'],
            'birth_at'              => ['nullable', 'date'],
            'birth_weight_grams'    => ['nullable', 'numeric', 'min:100', 'max:8000'],
            'birth_length_cm'       => ['nullable', 'numeric', 'min:20',  'max:80'],
            'head_circumference_cm' => ['nullable', 'numeric', 'min:15',  'max:60'],
            'gestational_age_weeks' => ['nullable', 'integer', 'min:20', 'max:45'],
            'apgar_1min'            => ['nullable', 'integer', 'min:0',  'max:10'],
            'apgar_5min'            => ['nullable', 'integer', 'min:0',  'max:10'],
            'delivery_type'         => ['nullable', 'in:Vaginal,C-Section,Assisted,Other'],
            'is_multiple_birth'     => ['sometimes', 'boolean'],
            'service_package_id'    => ['nullable', 'exists:service_packages,id'],
            'clinical_notes'        => ['nullable', 'string', 'max:2000'],
        ]);
    }

    /**
     * Shared admission creator — runs inside a transaction. Builds:
     *  1. Baby Patient row (parent_patient_id set if mother given)
     *  2. New CaseReference (parent_case_id chained from mother's case)
     *  3. NicuAdmission (with risk flags computed in model boot)
     *  4. Suggested bed allocation based on risk flags
     */
    protected function createAdmission(array $data, array $ctx): NicuAdmission
    {
        $mother      = $ctx['mother']      ?? null;
        $sourceType  = $ctx['source_type'];
        $sourceId    = $ctx['source_id']  ?? null;

        // Mother's case file — used for parent_case_id chain.
        $motherCaseId = null;
        if ($sourceType === NicuAdmission::SOURCE_OT) {
            $sched = OtSurgerySchedule::with('surgeryRequest')->find($sourceId);
            $motherCaseId = optional($sched?->surgeryRequest)->case_id;
        } elseif ($sourceType === NicuAdmission::SOURCE_IPD) {
            $motherCaseId = optional(IpdPatient::find($sourceId))->case_id;
        }

        // 1) Baby patient — name defaults to "B/o {mother.name}" if blank
        $babyName = $data['baby_name'] ?? null;
        if (! $babyName) {
            $babyName = 'Baby of ' . ($mother?->patient_name ?? 'Unknown Mother');
        }
        $baby = Patient::create([
            'patient_name'      => $babyName,
            'gender'            => $data['gender'],
            'dob'               => $data['birth_at'] ?? now(),
            'parent_patient_id' => $mother?->id,
            'birth_case_id'     => $motherCaseId,
            'is_active'         => true,
        ]);

        // 2) Case for the baby — chained to mother's
        $babyCase = CaseReference::create([
            'parent_case_id' => $motherCaseId,
        ]);

        // 3) Suggested bed based on risk flags computed momentarily
        $suggestedBed = $this->suggestBed($data);

        // 4) Create the admission
        $admission = NicuAdmission::create([
            'mother_patient_id'     => $mother?->id,
            'baby_patient_id'       => $baby->id,
            'case_id'               => $babyCase->id,
            'source_type'           => $sourceType,
            'source_id'             => $sourceId,
            'bed_id'                => $suggestedBed?->id,
            'bed_type_id'           => $suggestedBed?->bed_type_id,
            'admitted_at'           => now(),
            'birth_at'              => $data['birth_at'] ?? now(),
            'birth_weight_grams'    => $data['birth_weight_grams']    ?? null,
            'birth_length_cm'       => $data['birth_length_cm']       ?? null,
            'head_circumference_cm' => $data['head_circumference_cm'] ?? null,
            'gestational_age_weeks' => $data['gestational_age_weeks'] ?? null,
            'apgar_1min'            => $data['apgar_1min']            ?? null,
            'apgar_5min'            => $data['apgar_5min']            ?? null,
            'delivery_type'         => $data['delivery_type']         ?? null,
            'is_multiple_birth'     => (bool) ($data['is_multiple_birth'] ?? false),
            'service_package_id'    => $data['service_package_id']    ?? null,
            'clinical_notes'        => $data['clinical_notes']        ?? null,
            'status'                => NicuAdmission::STATUS_ADMITTED,
        ]);

        // Mark the chosen bed reserved
        if ($suggestedBed) {
            Bed::where('id', $suggestedBed->id)->update(['is_reserved' => true]);
        }

        // Auto-post the package charge if one was attached. Service is
        // idempotent — safe to call even when no package was selected.
        if ($admission->service_package_id) {
            app(PackageBillingService::class)->postChargeForNicu($admission->fresh());
        }

        return $admission;
    }

    /**
     * Pick a NICU bed based on the risk profile in the birth data.
     *
     * Rule (per BRD 4.2):
     *   - APGAR 5 < 7 (Critical) OR weight < 2500g (LBW) → Incubator
     *   - gestational < 37w (Preterm) without other red flags → Warmer
     *   - otherwise → first available NICU bed
     *
     * Falls back to any unreserved bed in an ICU-flagged bed_type if
     * dedicated NICU types aren't configured yet.
     */
    protected function suggestBed(array $data): ?Bed
    {
        $isCritical  = isset($data['apgar_5min'])         && $data['apgar_5min']         < 7;
        $isLbw       = isset($data['birth_weight_grams'])  && $data['birth_weight_grams']  < 2500;
        $isPreterm   = isset($data['gestational_age_weeks']) && $data['gestational_age_weeks'] < 37;

        $preferredNames = [];
        if ($isCritical || $isLbw)      $preferredNames = ['Incubator', 'NICU Incubator'];
        elseif ($isPreterm)             $preferredNames = ['Warmer', 'NICU Warmer'];
        else                            $preferredNames = ['NICU', 'NICU Bed', 'New Born'];

        // First try matching by bed_type name
        $bedTypeIds = BedType::whereIn('name', $preferredNames)->pluck('id');
        if ($bedTypeIds->isNotEmpty()) {
            $bed = Bed::whereIn('bed_type_id', $bedTypeIds)
                ->where('is_active', 1)
                ->where('is_reserved', 0)
                ->orderBy('id')
                ->first();
            if ($bed) return $bed;
        }

        // Fallback: any ICU-flagged bed type
        $icuTypeIds = BedType::where('is_icu', 1)->pluck('id');
        return Bed::whereIn('bed_type_id', $icuTypeIds)
            ->where('is_active', 1)
            ->where('is_reserved', 0)
            ->orderBy('id')
            ->first();
    }

    protected function gate(string $permission): void
    {
        if (auth()->check() && method_exists(auth()->user(), 'can') && ! auth()->user()->can($permission)) {
            abort(403, "Missing permission: {$permission}");
        }
    }
}
