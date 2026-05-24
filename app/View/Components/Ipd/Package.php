<?php

namespace App\View\Components\Ipd;

use App\Models\Package\PackageEnrollment;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Package extends Component
{
    public $iPDPatient;
    public $enrollments;
    public $linkedBills;

    public function __construct($iPDPatient)
    {
        $this->iPDPatient = $iPDPatient;

        $query = PackageEnrollment::with(['package.services.service', 'entries'])
            ->where('patient_id', $iPDPatient->patient_id);

        if ($iPDPatient->encounter_id) {
            $query->orWhere('encounter_id', $iPDPatient->encounter_id);
        }

        $this->enrollments = $query->latest('id')->get();

        $this->linkedBills = $iPDPatient->encounter_id
            ? \App\Models\Billing\Bill::where('encounter_id', $iPDPatient->encounter_id)
                ->latest('id')->get()
            : collect();
    }

    public function render(): View | Closure | string
    {
        return view('components.ipd.package');
    }
}
