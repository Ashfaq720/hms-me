<?php
namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\BedType;
use App\Models\Charges\Charge;
use App\Models\Department;
use App\Models\Package;
use App\Models\PackagePriceRule;
use App\Models\PackageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Master CRUD for the Package module.
 * Package services reference rows from the Hospital Charges master (/admin/charges).
 */
class PackageController extends Controller
{
    public function index(Request $request)
    {
        $packages = Package::query()
            ->with('bedType', 'department', 'services', 'priceRules')
            ->when($request->string('q')->toString(), function ($q, $term) {
                $q->where(fn ($w) => $w->where('name', 'like', "%$term%")
                    ->orWhere('code', 'like', "%$term%"));
            })
            ->when($request->string('type')->toString(), fn ($q, $t) => $q->where('package_type', $t))
            ->when($request->string('status')->toString(), fn ($q, $s) => $q->where('status', $s))
            ->latest('id')->paginate(25)->withQueryString();

        // Counts per package type for filter chips
        $typeCounts = Package::query()
            ->select('package_type', \Illuminate\Support\Facades\DB::raw('COUNT(*) as n'))
            ->groupBy('package_type')->pluck('n', 'package_type')->toArray();

        $stats = [
            'total'     => Package::count(),
            'active'    => Package::where('status', 'active')->count(),
            'enrolments'=> \App\Models\Package\PackageEnrollment::count(),
            'revenue'   => (float) \App\Models\Package\PackageEnrollment::sum('paid_amount'),
        ];

        return view('packages.index', compact('packages', 'typeCounts', 'stats'));
    }

    public function create()
    {
        return view('packages.create', $this->formData(new Package()));
    }

    public function show($id)
    {
        $package = Package::with([
            'services.charge.chargeType',
            'services.charge.chargeCategory',
            'services.charge.uniteType',
            'services.catalog',
            'priceRules.bedType',
            'enrollments.patient',
            'bedType',
            'department',
        ])->findOrFail($id);
        return view('packages.show', compact('package'));
    }

    public function store(Request $request)
    {
        $data = $this->validatePackage($request);
        DB::transaction(function () use ($request, $data) {
            $package = Package::create($data + ['total_amount' => 0]);
            $subtotal = $this->saveServices($package, $request->input('items', []));
            $this->savePriceRules($package, $request->input('rules', []));
            $this->saveBedQuickPrices($package, $request->input('bed_quick', []));
            $this->saveRoomOverrides($package, $request->input('room_override', []));
            $package->update(['total_amount' => $subtotal]);
        });
        return redirect()->route('packages.index')->with('success', 'Package created.');
    }

    public function edit($id)
    {
        $package = Package::with(['services.charge', 'priceRules.bedType', 'priceRules.department'])->findOrFail($id);
        return view('packages.edit', $this->formData($package));
    }

    public function update(Request $request, $id)
    {
        $package = Package::findOrFail($id);
        $data = $this->validatePackage($request, $id);
        DB::transaction(function () use ($request, $package, $data) {
            $package->update($data);
            // Refresh services + rules + room overrides
            PackageService::where('package_id', $package->id)->delete();
            PackagePriceRule::where('package_id', $package->id)->delete();
            \App\Models\PackageBedLink::where('package_id', $package->id)->delete();
            $subtotal = $this->saveServices($package, $request->input('items', []));
            $this->savePriceRules($package, $request->input('rules', []));
            $this->saveBedQuickPrices($package, $request->input('bed_quick', []));
            $this->saveRoomOverrides($package, $request->input('room_override', []));
            $package->update(['total_amount' => $subtotal]);
        });
        return redirect()->route('packages.index')->with('success', 'Package updated.');
    }

    /**
     * Persist optional Room-level price overrides into package_bed_links.
     * $rows = [ room_id => override_price, ... ]; blank/zero = skip.
     */
    private function saveRoomOverrides(Package $package, array $rows): void
    {
        foreach ($rows as $roomId => $price) {
            if ($price === '' || $price === null) continue;
            $price = (float) $price;
            if ($price <= 0) continue;
            $room = \App\Models\Room::find($roomId);
            if (! $room) continue;
            \App\Models\PackageBedLink::create([
                'package_id'     => $package->id,
                'bed_type_id'    => null,
                'room_id'        => $room->id,
                'bed_id'         => null,
                'override_price' => $price,
                'is_default'     => false,
            ]);
        }
    }

    public function destroy($id)
    {
        $package = Package::findOrFail($id);
        $package->update(['status' => 'archived', 'is_active' => false]);
        return back()->with('success', 'Package archived (kept for historical enrolments).');
    }

    // ──────────── helpers ────────────

    private function formData(Package $package): array
    {
        // Hospital Charges master is the canonical source for package items.
        $charges = Charge::with(['chargeType', 'chargeCategory', 'uniteType'])
            ->orderBy('charge_name')
            ->get();

        // Enrich bed types with stats from the Bed Management hierarchy.
        $bedTypes = BedType::withCount('beds')
            ->with(['beds' => fn ($q) => $q->select('id', 'bed_type_id', 'rent', 'room_id', 'status')])
            ->orderBy('name')->get();

        // Rooms grouped by floor + ward — used by the optional Room-level price override.
        $rooms = \App\Models\Room::with(['bedGroup.floor'])
            ->where('is_active', true)
            ->orderBy('floor_id')->orderBy('room_no')
            ->get();

        // Existing room-level overrides for this package (if editing)
        $bedLinks = $package->exists
            ? \App\Models\PackageBedLink::where('package_id', $package->id)->get()->keyBy('room_id')
            : collect();

        return [
            'package'    => $package,
            'charges'    => $charges,
            'bedTypes'   => $bedTypes,
            'rooms'      => $rooms,
            'bedLinks'   => $bedLinks,
            'departments'=> Department::orderBy('name')->get(['id', 'name']),
            'types'      => Package::categories(),
        ];
    }

    private function validatePackage(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name'              => 'required|string|max:191',
            'code'              => 'nullable|string|max:32|unique:packages,code,' . ($id ?: 'NULL'),
            'description'       => 'nullable|string|max:2000',
            'package_type'      => 'required|string|max:32',
            'admission_type'    => 'nullable|in:PLANNED,EMERGENCY,DAY_CARE,WALK_IN,ANY',
            'department_id'     => 'nullable|exists:departments,id',
            'bed_type_id'       => 'nullable|exists:bed_types,id',
            'patient_type'      => 'nullable|string|max:32',
            'validity_days'     => 'nullable|integer|min:1|max:365',
            'discount'          => 'nullable|numeric|min:0|max:100',
            'is_active'         => 'nullable|boolean',
            'status'            => 'nullable|in:active,inactive,archived',
        ]);
    }

    private function saveServices(Package $package, array $items): float
    {
        $subtotal = 0;
        foreach ($items as $row) {
            $chargeId = $row['charge_id'] ?? null;
            if (! $chargeId) continue;

            $qty = (float) ($row['quantity'] ?? 1);
            $rate = (float) ($row['rate'] ?? 0);
            $included = ! empty($row['is_included']);
            $amount = $included ? $qty * $rate : 0;
            $subtotal += $amount;

            PackageService::create([
                'package_id'         => $package->id,
                'charge_id'          => (int) $chargeId,
                'service_catalog_id' => null,
                'service_id'         => null,
                'is_included'        => $included,
                'quantity'           => $qty,
                'rate'               => $rate,
                'amount'             => $amount,
                'note'               => $row['note'] ?? null,
            ]);
        }
        return $subtotal;
    }

    private function savePriceRules(Package $package, array $rules): void
    {
        foreach ($rules as $r) {
            if (! isset($r['price']) || $r['price'] === '') continue;
            PackagePriceRule::create([
                'package_id'      => $package->id,
                'bed_type_id'     => $r['bed_type_id'] ?: null,
                'department_id'   => $r['department_id'] ?: null,
                'duration_days'   => $r['duration_days'] ?: null,
                'patient_category'=> $r['patient_category'] ?? 'ANY',
                'price'           => (float) $r['price'],
                'valid_from'      => $r['valid_from'] ?: null,
                'valid_to'        => $r['valid_to'] ?: null,
                'is_active'       => true,
                'notes'           => $r['notes'] ?? null,
                'created_by'      => auth()->id(),
            ]);
        }
    }

    /**
     * Bed-wise Quick Pricing — converts $bed_quick[$bedTypeId] = $price
     * into one PackagePriceRule per non-blank entry.
     * Blank = use base price (no rule created).
     */
    private function saveBedQuickPrices(Package $package, array $bedQuick): void
    {
        foreach ($bedQuick as $bedTypeId => $price) {
            if ($price === '' || $price === null) continue;
            $price = (float) $price;
            if ($price <= 0) continue;
            PackagePriceRule::create([
                'package_id'      => $package->id,
                'bed_type_id'     => (int) $bedTypeId,
                'patient_category'=> 'ANY',
                'price'           => $price,
                'is_active'       => true,
                'notes'           => 'Bed-wise quick price',
                'created_by'      => auth()->id(),
            ]);
        }
    }
}
