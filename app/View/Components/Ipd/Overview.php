<?php
namespace App\View\Components\Ipd;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\FrontDesk\VitalCheck;

class Overview extends Component
{
    public $iPDPatient;
    public $latestVital;

    public function __construct($iPDPatient)
    {
        $this->iPDPatient = $iPDPatient;
        $this->latestVital = VitalCheck::where('ipd_patient_id', $iPDPatient->id)
            ->latest('checked_at')
            ->first();
    }

    public function render(): View | Closure | string
    {
        return view('components.ipd.overview');
    }
}
