<?php
namespace App\View\Components\Ipd;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class Charges extends Component
{
    public $charges;
    public $iPDPatient;
    public $serviceChargePostings;
    public $chargeTotal;
    public $packageCoveredTotal;
    public $packageEnrollments;
    public $bills;
    public $totalBillGrand;
    public $totalBillPaid;
    public $totalBillDue;

    public function __construct($iPDPatient)
    {
        $this->iPDPatient = $iPDPatient;

        // 1. Legacy patient_charges rows (manually added charges)
        $legacy = ($iPDPatient->charges ?? collect())->map(function ($c) {
            $c->is_auto = false;
            $c->source = 'Manual Charge';
            $c->is_package_included = false;
            return $c;
        });

        // 2. Active package enrollments — used to detect package-covered postings
        $this->packageEnrollments = $iPDPatient->encounter_id
            ? \App\Models\Package\PackageEnrollment::with('package.services.service')
                ->where(function ($q) use ($iPDPatient) {
                    $q->where('encounter_id', $iPDPatient->encounter_id)
                        ->orWhere('patient_id', $iPDPatient->patient_id);
                })
                ->where('status', 'active')
                ->get()
            : collect();

        $packageServiceNames = $this->packageEnrollments
            ->flatMap(fn ($e) => $e->package->services->pluck('service.name'))
            ->filter()
            ->map(fn ($n) => strtolower(trim($n)))
            ->unique()
            ->values();

        // 3. Auto-posted service-charge postings from observers
        $auto = collect();
        if ($iPDPatient->encounter_id) {
            $auto = \App\Models\ServiceCharge\ServiceChargePosting::with('catalog')
                ->where('encounter_id', $iPDPatient->encounter_id)
                ->where('status', 'posted')
                ->latest('id')
                ->get()
                ->map(function ($p) use ($packageServiceNames) {
                    $catalogName = optional($p->catalog)->name ?? '';
                    $isCovered = $packageServiceNames->isNotEmpty()
                        && $packageServiceNames->contains(fn ($n) => str_contains(strtolower($catalogName), $n));

                    return (object) [
                        'id'           => 'sc-' . $p->id,
                        'date'         => $p->created_at,
                        'charge_id'    => null,
                        'charge_item'  => $catalogName ?: $p->trigger_event,
                        'unit_price'   => $p->unit_price,
                        'quantity'     => $p->quantity,
                        'amount'       => (float) $p->unit_price * (float) $p->quantity,
                        'vat'          => 0,
                        'tax'          => $p->tax_amount,
                        'net_amount'   => $p->net_amount,
                        'is_auto'      => true,
                        'is_package_included' => $isCovered,
                        'source'       => 'Service Charge Engine',
                    ];
                });
        }

        $merged = (new Collection())->merge($legacy)->merge($auto);
        $this->charges = $merged;
        $this->chargeTotal = (float) $merged->sum('net_amount');
        $this->packageCoveredTotal = (float) $merged->where('is_package_included', true)->sum('net_amount');
        $this->serviceChargePostings = $auto;

        // 4. Assembled bills from the encounter layer
        $this->bills = $iPDPatient->encounter_id
            ? \App\Models\Billing\Bill::where('encounter_id', $iPDPatient->encounter_id)
                ->latest('id')->get()
            : collect();
        $this->totalBillGrand = (float) $this->bills->sum('grand_total');
        $this->totalBillPaid  = (float) $this->bills->sum('paid_total');
        $this->totalBillDue   = (float) $this->bills->sum('balance_due');
    }

    public function render(): View | Closure | string
    {
        return view('components.ipd.charges');
    }
}
