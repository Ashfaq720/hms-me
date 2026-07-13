<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use App\Models\BedType;
use App\Models\Department;
use App\Models\Ot\OtConsumable;
use App\Models\Ot\OtEquipment;
use App\Models\Ot\OtSurgeryCategory;
use App\Models\Ot\OtSurgeryType;
use App\Models\ServicePackage;
use App\Models\ServicePackageBedPrice;
use App\Models\ServicePackageItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ServicePackageController extends Controller
{
    public function index(Request $request)
    {
        $this->gate('service_packages_access');

        $q = ServicePackage::with(['department', 'bedType'])
            ->withCount([
                'items',
                'items as items_with_charge_count' => fn ($x) => $x->where('master_type', 'charge'),
                'bedPrices',
                'applications',
                'patientCharges',
            ])
            ->withSum('patientCharges as revenue_posted', 'net_amount');

        if ($type = $request->get('package_type')) {
            $q->ofType($type);
        }
        if ($dept = $request->get('department_id')) {
            $q->where('department_id', $dept);
        }
        if ($bedType = $request->get('bed_type_id')) {
            // Match packages whose default bed_type OR any bed-price variant
            // points at this bed type.
            $q->where(function ($x) use ($bedType) {
                $x->where('bed_type_id', $bedType)
                  ->orWhereHas('bedPrices', fn ($y) => $y->where('bed_type_id', $bedType));
            });
        }
        if (in_array($request->get('status'), ServicePackage::STATUSES, true)) {
            $q->where('status', $request->get('status'));
        }
        if ($s = trim((string) $request->get('search'))) {
            $q->where(function ($x) use ($s) {
                $x->where('code', 'like', "%{$s}%")
                  ->orWhere('name', 'like', "%{$s}%");
            });
        }

        $packages    = $q->latest('id')->paginate(20)->appends($request->query());
        $types       = ServicePackage::TYPES;
        $departments = Department::orderBy('name')->get(['id', 'name']);

        return view('setup.service-packages.index', compact('packages', 'types', 'departments'));
    }

    public function create()
    {
        $this->gate('service_packages_create');
        return view('setup.service-packages.create', $this->formData());
    }

    public function store(Request $request)
    {
        $this->gate('service_packages_create');
        $data = $this->validated($request);

        DB::transaction(function () use ($request, $data, &$package) {
            $package = ServicePackage::create($data);
            $this->syncItems($package, $request->input('items', []));
            $this->syncBedPrices($package, $request->input('bed_prices', []));
        });

        return redirect()
            ->route('setup.service-packages.show', $package)
            ->with('success', "Package {$package->code} created.");
    }

    public function show(ServicePackage $service_package)
    {
        $this->gate('service_packages_access');
        $service_package->load([
            'department', 'bedType',
            'items', 'bedPrices.bedType',
            'createdBy', 'updatedBy',
            'applications.ipdAdmission.patient:id,patient_name,mrn',
            'applications.bedAllocation.bed:id,name,bed_type_id',
            'patientCharges' => fn ($q) => $q->latest('date')->limit(50),
        ]);

        // Bed counts per bed type that this package targets (default + variants)
        $bedTypeIds = collect([$service_package->bed_type_id])
            ->merge($service_package->bedPrices->pluck('bed_type_id'))
            ->filter()->unique();

        $bedAvailability = \App\Models\Bed::selectRaw('bed_type_id, count(*) total, sum(case when status in (?, ?) and is_active = 1 then 1 else 0 end) as available', [
                \App\Models\Bed::STATUS_AVAILABLE,
                \App\Models\Bed::STATUS_READY,
            ])
            ->whereIn('bed_type_id', $bedTypeIds)
            ->groupBy('bed_type_id')
            ->get()
            ->keyBy('bed_type_id');

        return view('setup.service-packages.show', [
            'package'         => $service_package,
            'bedAvailability' => $bedAvailability,
        ]);
    }

    public function edit(ServicePackage $service_package)
    {
        $this->gate('service_packages_edit');
        $service_package->load(['items', 'bedPrices']);
        return view('setup.service-packages.edit', array_merge(
            $this->formData(),
            ['package' => $service_package]
        ));
    }

    public function update(Request $request, ServicePackage $service_package)
    {
        $this->gate('service_packages_edit');
        $data = $this->validated($request, $service_package);

        DB::transaction(function () use ($request, $service_package, $data) {
            $service_package->update($data);
            $this->syncItems($service_package, $request->input('items', []));
            $this->syncBedPrices($service_package, $request->input('bed_prices', []));
        });

        return redirect()
            ->route('setup.service-packages.show', $service_package)
            ->with('success', "Package {$service_package->code} updated.");
    }

    public function destroy(ServicePackage $service_package)
    {
        $this->gate('service_packages_delete');
        $service_package->delete();
        return redirect()
            ->route('setup.service-packages.index')
            ->with('success', "Package {$service_package->code} archived.");
    }

    /* ────────── helpers ────────── */

    protected function formData(): array
    {
        // Generic Service master — only loaded if the model exists and
        // it has rows. Keeps the form clean if the service master is
        // unused on this deployment.
        $services = collect();
        if (class_exists(\App\Models\Service::class)) {
            $services = \App\Models\Service::query()
                ->when(\Illuminate\Support\Facades\Schema::hasColumn('services', 'status'),
                    fn ($q) => $q->where('status', '!=', 'inactive'))
                ->orderBy('name')
                ->get(['id', 'name', 'rate']);
        }

        // Charge master (e.g. Doctor visit charge, OT room charge, etc.).
        // Lets package items reference real billable charge codes.
        $charges = collect();
        if (class_exists(\App\Models\Charges\Charge::class)) {
            $charges = \App\Models\Charges\Charge::query()
                ->orderBy('charge_name')
                ->get(['id', 'charge_name as name', 'standard_charge as rate']);
        }

        return [
            'types'              => ServicePackage::TYPES,
            'admissionTypes'     => ServicePackage::ADMISSION_TYPES,
            'patientCategories'  => ServicePackage::PATIENT_CATEGORIES,
            'statuses'           => ServicePackage::STATUSES,
            'itemCategories'     => ServicePackageItem::CATEGORIES,
            'masterTypes'        => ServicePackageItem::MASTER_TYPES,
            'departments'        => Department::orderBy('name')->get(['id', 'name']),
            // Enriched bed-types: include bed counts + ICU/isolation flags
            // so the package form can group, show availability, and visually
            // mark the default bed type in the Bed-wise Price section.
            'bedTypes'           => BedType::withCount([
                                        'beds',
                                        'beds as beds_available_count' => function ($q) {
                                            $q->whereIn('status', [
                                                \App\Models\Bed::STATUS_AVAILABLE,
                                                \App\Models\Bed::STATUS_READY,
                                            ])->where('is_active', 1);
                                        },
                                    ])
                                    ->orderBy('name')->get(),
            'surgeryTypes'       => OtSurgeryType::where('is_active', 1)->orderBy('name')->get(['id', 'name']),
            'surgeryCategories'  => OtSurgeryCategory::orderBy('name')->get(['id', 'name']),
            'consumables'        => OtConsumable::where('is_active', 1)->orderBy('name')->get(['id', 'name', 'unit', 'rate']),
            'equipments'         => OtEquipment::orderBy('name')->get(['id', 'name']),
            'services'           => $services,
            'charges'            => $charges,
            // Lab Investigations — drives the "Investigation" item picker.
            // Includes category name so users see what kind of test it is.
            'labInvestigations'  => \App\Models\LabInvestigation::query()
                                        ->where('status', 1)
                                        ->with('category:id,name')
                                        ->orderBy('name')
                                        ->get(['id', 'name', 'short_name', 'category_id', 'price']),
        ];
    }

    protected function validated(Request $request, ?ServicePackage $existing = null): array
    {
        return $request->validate([
            'code'                   => ['nullable', 'string', 'max:50',
                                          Rule::unique('service_packages', 'code')->ignore($existing?->id)],
            'name'                   => ['required', 'string', 'max:200'],
            'package_type'           => ['required', Rule::in(ServicePackage::TYPES)],
            'department_id'          => ['nullable', 'exists:departments,id'],
            'admission_type'         => ['nullable', Rule::in(ServicePackage::ADMISSION_TYPES)],
            'bed_type_id'            => ['nullable', 'exists:bed_types,id'],
            'surgery_type_id'        => ['nullable', 'exists:ot_surgery_types,id'],
            'surgery_category_id'    => ['nullable', 'exists:ot_surgery_categories,id'],
            'duration_days'          => ['nullable', 'integer', 'min:0', 'max:365'],
            'base_price'             => ['required', 'numeric', 'min:0'],
            'included_services_text' => ['nullable', 'string'],
            'excluded_services_text' => ['nullable', 'string'],
            'patient_category'       => ['nullable', Rule::in(ServicePackage::PATIENT_CATEGORIES)],
            'requires_approval'      => ['sometimes', 'boolean'],
            'approval_role'          => ['nullable', 'string', 'max:60'],
            'status'                 => ['required', Rule::in(ServicePackage::STATUSES)],
            'remarks'                => ['nullable', 'string'],
        ]);
    }

    /**
     * Replace items in one shot (simpler than diff). Items table is small.
     */
    protected function syncItems(ServicePackage $package, array $rows): void
    {
        $package->items()->delete();
        $sort = 0;
        foreach ($rows as $row) {
            $name = trim((string) ($row['item_name'] ?? ''));
            if ($name === '') continue;

            $masterType = in_array(($row['master_type'] ?? null), ServicePackageItem::MASTER_TYPES, true)
                ? $row['master_type'] : null;
            $masterId   = is_numeric($row['master_id'] ?? null) ? (int) $row['master_id'] : null;
            // master_type without master_id (or vice versa) → drop the link to keep data clean
            if (! $masterType || ! $masterId) { $masterType = null; $masterId = null; }

            ServicePackageItem::create([
                'service_package_id' => $package->id,
                'item_category'      => in_array(($row['item_category'] ?? null), ServicePackageItem::CATEGORIES, true)
                                          ? $row['item_category'] : 'Other',
                'master_type'        => $masterType,
                'master_id'          => $masterId,
                'item_name'          => $name,
                'included_qty'       => (float) ($row['included_qty'] ?? 1),
                'unit'               => $row['unit']  ?? null,
                'notes'              => $row['notes'] ?? null,
                'sort_order'         => $sort++,
            ]);
        }
    }

    /**
     * Replace bed-wise prices. Each row keyed by bed_type_id; blank price
     * = remove that bed-type override.
     */
    protected function syncBedPrices(ServicePackage $package, array $rows): void
    {
        $package->bedPrices()->delete();
        foreach ($rows as $bedTypeId => $price) {
            $price = is_numeric($price) ? (float) $price : null;
            if ($price === null || $price < 0) continue;
            ServicePackageBedPrice::create([
                'service_package_id' => $package->id,
                'bed_type_id'        => (int) $bedTypeId,
                'price'              => $price,
            ]);
        }
    }

    /**
     * Permission gate. Skipped silently if the named permission doesn't
     * exist yet in this environment — the seeder will add it.
     */
    protected function gate(string $permission): void
    {
        if (auth()->check() && method_exists(auth()->user(), 'can') && ! auth()->user()->can($permission)) {
            abort(403, "Missing permission: {$permission}");
        }
    }
}
