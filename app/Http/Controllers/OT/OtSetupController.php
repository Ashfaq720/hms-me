<?php

namespace App\Http\Controllers\OT;

use App\Http\Controllers\Controller;
use App\Models\Ot\OtAnesthesiaType;
use App\Models\Ot\OtConsumable;
use App\Models\Ot\OtEquipment;
use App\Models\Ot\OtRoom;
use App\Models\Ot\OtSurgeryCategory;
use App\Models\Ot\OtSurgeryType;

class OtSetupController extends OtBaseController
{
    public function index()
    {
        $this->gate('ot_setup_access');
        $counts = [
            'rooms' => OtRoom::count(),
            'equipments' => OtEquipment::count(),
            'surgery_categories' => OtSurgeryCategory::count(),
            'anesthesia_types' => OtAnesthesiaType::count(),
            'surgery_types' => OtSurgeryType::count(),
            'consumables' => OtConsumable::count(),
        ];

        return view('ot.setup.index', compact('counts'));
    }
}
