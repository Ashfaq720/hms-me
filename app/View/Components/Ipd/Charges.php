<?php
namespace App\View\Components\Ipd;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Charges extends Component
{
    /**
     * Create a new component instance.
     */
    public $charges;
    public $iPDPatient;
    public function __construct($iPDPatient)
    {
        $this->iPDPatient = $iPDPatient;
        $this->charges = $iPDPatient->charges;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View | Closure | string
    {
        return view('components.ipd.charges');
    }
}
