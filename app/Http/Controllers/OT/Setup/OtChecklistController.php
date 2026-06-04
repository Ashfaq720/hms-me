<?php

namespace App\Http\Controllers\OT\Setup;

use App\Http\Controllers\Controller;

class OtChecklistController extends Controller
{
    /**
     * Pre-op / time-out / sign-out checklist master list.
     * The actual checklist items are stored as JSON on ot_surgery_schedules
     * (pre_op_checklist column); this page lists the default template items.
     */
    public function index()
    {
        $checklists = [
            ['phase' => 'Pre-Op (Sign-In)', 'items' => [
                'Patient identity confirmed (name + MRN)',
                'Surgical site marked & verified',
                'Consent signed by patient/guardian',
                'Allergies reviewed and documented',
                'Anaesthesia safety check completed',
                'Pulse-oximeter functioning',
                'Anticipated airway / aspiration risk',
                'Risk of >500ml blood loss (>7ml/kg in children)',
            ]],
            ['phase' => 'Time-Out (Before Skin Incision)', 'items' => [
                'All team members introduced themselves',
                'Surgeon, anaesthetist, nurse confirm patient, site, procedure',
                'Antibiotic prophylaxis given in last 60 min',
                'Anticipated critical events reviewed (surgeon)',
                'Anaesthesia concerns reviewed',
                'Sterility of instrumentation confirmed',
                'Imaging displayed (if applicable)',
            ]],
            ['phase' => 'Sign-Out (Before Patient Leaves OR)', 'items' => [
                'Procedure recorded in OT register',
                'Instrument / sponge / needle counts correct',
                'Specimens labelled correctly (with patient name)',
                'Equipment problems addressed',
                'Surgeon + anaesthetist agree on recovery and post-op plan',
            ]],
        ];

        return view('ot.setup.checklists.index', compact('checklists'));
    }

    public function create()
    {
        return redirect()->route('ot.setup.checklists.index')
            ->with('info', 'Checklist templates are seeded. Edit via the configuration JSON in app/Config/ot_checklists.php');
    }
}
