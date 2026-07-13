<?php

namespace App\View\Components\Ipd;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Prescriptions extends Component
{
    public $prescriptions;
    public $iPDPatient;

    public function __construct($iPDPatient)
    {
        $this->iPDPatient = $iPDPatient;
        $this->prescriptions = $iPDPatient->prescriptions()->with(['doctor', 'symptoms.symptom', 'medicines', 'labInvestigations.labInvestigation'])->latest()->get();
    }

    public function render(): View|Closure|string
    {
        return view('components.ipd.prescriptions');
    }
}
