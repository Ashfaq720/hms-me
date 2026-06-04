<?php
namespace App\View\Components\Ipd;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class BedHistory extends Component
{
    /**
     * Create a new component instance.
     */

    public $bedAllocations;
    public $iPDPatient;
    public function __construct($bedAllocations, $iPDPatient = null)
    {
        $this->bedAllocations = $bedAllocations;
        $this->iPDPatient = $iPDPatient;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View | Closure | string
    {
        return view('components.ipd.bed-history');
    }
}
