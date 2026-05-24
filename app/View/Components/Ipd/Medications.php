<?php

namespace App\View\Components\Ipd;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Medications extends Component
{
    public $iPDPatient;

    public function __construct($iPDPatient)
    {
        $this->iPDPatient = $iPDPatient;
    }

    public function render(): View|Closure|string
    {
        return view('components.ipd.medications');
    }
}
