<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

/**
 * Hospital Command Centers — landing pages that gather scattered modules
 * under one organised hub. Each "center" is a curated dashboard with
 * counts + direct deep-links to the underlying CRUD screens.
 *
 *   /admin/master-data       Master Data Center  (30+ master tables)
 *   /admin/equipment-center  Equipment Center    (OT + ICU + NICU + CCU + Ambulance)
 *   /admin/inventory-hub     Inventory Hub       (items + warehouses + stock movements + pharmacy)
 *   /admin/billing-center    Billing Center      (all 8 legacy billing types + unified bills)
 *   /admin/clinical-center   Clinical Center     (OPD/IPD/ER/ICU/NICU/CCU/OT/PACU access)
 */
class CommandCenterController extends Controller
{
    public function __construct() { $this->middleware('auth'); }

    public function masterData()
    {
        $sections = $this->masterDataMap();
        $counts = [];
        foreach ($sections as $group => $items) {
            foreach ($items as $key => $cfg) {
                $counts[$key] = $this->safeCount($cfg['table'] ?? null);
            }
        }
        return view('admin.centers.master-data', compact('sections', 'counts'));
    }

    public function equipment()
    {
        $stats = [
            'ot_equipments'       => $this->safeCount('ot_equipments'),
            'icu_equipment'       => $this->safeCount('icu_equipment'),
            'amb_equipment'       => $this->safeCount('amb_ambulance_equipment'),
            'ot_consumables'      => $this->safeCount('ot_consumables'),
            'inventory_items'     => $this->safeCount('inventory_items'),
            'inventory_assets'    => $this->safeCount('inventory_items') > 0
                ? DB::table('inventory_items')->where('is_asset', true)->count() : 0,
            'inventory_consumes'  => $this->safeCount('inventory_items') > 0
                ? DB::table('inventory_items')->where('is_consumable', true)->count() : 0,
        ];

        $otEquipment = DB::table('ot_equipments')
            ->leftJoin('ot_rooms', 'ot_rooms.id', '=', 'ot_equipments.ot_room_id')
            ->select('ot_equipments.*', 'ot_rooms.name as room_name')
            ->orderBy('ot_equipments.name')->limit(50)->get();
        $icuEquipment = DB::table('icu_equipment')->orderBy('equipment_name')->limit(50)->get();

        $byCategory = DB::table('ot_equipments')->select('category', DB::raw('COUNT(*) as n'))
            ->groupBy('category')->orderByDesc('n')->get();
        $byIcuType = DB::table('icu_equipment')->select('icu_type', DB::raw('COUNT(*) as n'))
            ->groupBy('icu_type')->get();

        return view('admin.centers.equipment', compact('stats', 'otEquipment', 'icuEquipment', 'byCategory', 'byIcuType'));
    }

    public function inventoryHub()
    {
        $stats = [
            'items_total'    => $this->safeCount('inventory_items'),
            'warehouses'     => $this->safeCount('inventory_warehouses'),
            'batches'        => $this->safeCount('inventory_item_batches'),
            'movements'      => $this->safeCount('stock_movements'),
            'medicines'      => $this->safeCount('medicines'),
            'consumables'    => $this->safeCount('inventory_items') > 0
                                  ? DB::table('inventory_items')->where('is_consumable', true)->count() : 0,
            'controlled'     => $this->safeCount('inventory_items') > 0
                                  ? DB::table('inventory_items')->where('is_controlled', true)->count() : 0,
            'pharma_tx'      => $this->safeCount('pharmacy_transactions'),
        ];
        $recent = DB::table('stock_movements')
            ->leftJoin('inventory_items as i', 'i.id', '=', 'stock_movements.inventory_item_id')
            ->select('stock_movements.*', 'i.name as item_name', 'i.code as item_code')
            ->latest('stock_movements.id')->limit(15)->get();
        $byDirection = DB::table('stock_movements')->select('direction', DB::raw('COUNT(*) as n'))
            ->groupBy('direction')->get()->pluck('n', 'direction')->toArray();

        return view('admin.centers.inventory', compact('stats', 'recent', 'byDirection'));
    }

    public function billing()
    {
        $stats = [
            'bills_total'    => $this->safeCount('bills'),
            'paid'           => DB::table('bills')->where('status', 'paid')->count(),
            'final'          => DB::table('bills')->where('status', 'final')->count(),
            'partial'        => DB::table('bills')->where('status', 'partially_paid')->count(),
            'draft'          => DB::table('bills')->where('status', 'draft')->count(),
            'grand_total'    => (float) DB::table('bills')->sum('grand_total'),
            'paid_total'     => (float) DB::table('bills')->sum('paid_total'),
            'outstanding'    => (float) DB::table('bills')->sum('balance_due'),
            'payments'       => $this->safeCount('bill_payments'),
            'claims'         => $this->safeCount('claims'),
        ];
        $byType = DB::table('bills')->select('bill_type', DB::raw('COUNT(*) as n'),
                DB::raw('SUM(grand_total) as gross'), DB::raw('SUM(paid_total) as paid'))
            ->groupBy('bill_type')->orderByDesc('gross')->get();
        return view('admin.centers.billing', compact('stats', 'byType'));
    }

    public function clinical()
    {
        $stats = [
            'opd_today'      => DB::table('opd_patients')->whereDate('date', today())->count(),
            'opd_total'      => $this->safeCount('opd_patients'),
            'ipd_admitted'   => DB::table('i_p_d_patients')->where('status', 'admitted')->count(),
            'ipd_total'      => $this->safeCount('i_p_d_patients'),
            'er_today'       => DB::table('er_patients')->whereDate('arrival_time', today())->count(),
            'er_total'       => $this->safeCount('er_patients'),
            'icu_admitted'   => DB::table('icu_admissions')->where('status', 'Admitted')->count(),
            'nicu_admitted'  => DB::table('nicu_admissions')->where('status', 'admitted')->count(),
            'ot_scheduled'   => $this->safeCount('ot_surgery_schedules'),
            'ot_requests'    => $this->safeCount('ot_surgery_requests'),
            'patients'       => $this->safeCount('patients'),
            'encounters'     => $this->safeCount('encounters'),
        ];
        return view('admin.centers.clinical', compact('stats'));
    }

    private function masterDataMap(): array
    {
        return [
            'Organization' => [
                'orgs'   => ['label' => 'Organizations',   'route' => 'organizations.index',    'table' => 'organizations',     'icon' => 'building'],
                'branch' => ['label' => 'Branches',        'route' => 'branches.index',         'table' => 'branches',          'icon' => 'diagram-3'],
                'dept'   => ['label' => 'Departments',     'route' => 'departments.index',      'table' => 'departments',       'icon' => 'briefcase'],
                'desig'  => ['label' => 'Designations',    'route' => 'designations.index',     'table' => 'designations',      'icon' => 'person-badge'],
            ],
            'Staff & Roles' => [
                'doc'    => ['label' => 'Doctors',         'route' => 'doctors.index',          'table' => 'doctors',           'icon' => 'person-vcard'],
                'spec'   => ['label' => 'Specialists',     'route' => 'specialists.index',      'table' => 'specialists',       'icon' => 'mortarboard'],
                'fee'    => ['label' => 'Doctor Fees',     'route' => 'doctor-fees.index',      'table' => 'doctor_fees',       'icon' => 'cash'],
                'role'   => ['label' => 'Roles',           'route' => 'roles.index',            'table' => 'roles',             'icon' => 'shield-check'],
            ],
            'Beds & Rooms' => [
                'floor'  => ['label' => 'Floors',          'route' => 'floor.index',            'table' => 'floors',            'icon' => 'layers'],
                'bgrp'   => ['label' => 'Bed Groups',      'route' => 'bed-groups.index',       'table' => 'bed_groups',        'icon' => 'grid-3x3'],
                'btype'  => ['label' => 'Bed Types',       'route' => 'bed-types.index',        'table' => 'bed_types',         'icon' => 'tag'],
                'bed'    => ['label' => 'Beds',            'route' => 'beds.index',             'table' => 'beds',              'icon' => 'house'],
            ],
            'Pricing & Packages' => [
                'svc'    => ['label' => 'Service Catalog', 'route' => 'service-charge.catalog.index', 'table' => 'service_catalogs', 'icon' => 'coin'],
                'pkg'    => ['label' => 'Packages',        'route' => 'packages.index',         'table' => 'packages',          'icon' => 'box-seam'],
            ],
            'Pharmacy & Medicine' => [
                'mcat'   => ['label' => 'Medicine Categories', 'route' => 'admin.medicine-categories.index', 'table' => 'medicine_categories', 'icon' => 'collection'],
                'mgen'   => ['label' => 'Generics',         'route' => 'admin.medicine-generics.index',    'table' => 'medicine_generics',   'icon' => 'capsule'],
                'mgrp'   => ['label' => 'Medical Groups',   'route' => 'admin.medical-groups.index',       'table' => 'medical_groups',      'icon' => 'tags'],
                'comp'   => ['label' => 'Companies',        'route' => 'admin.companies.index',            'table' => 'companies',           'icon' => 'building'],
                'med'    => ['label' => 'Medicines',        'route' => 'admin.medicines.index',            'table' => 'medicines',           'icon' => 'capsule-pill'],
            ],
            'Lab & Diagnostics' => [
                'ltype'  => ['label' => 'Lab Types',        'route' => 'lab-investigation-types.index',      'table' => 'lab_investigation_types', 'icon' => 'eyedropper'],
                'lcat'   => ['label' => 'Lab Categories',   'route' => 'lab-investigation-categories.index', 'table' => 'lab_investigation_categories', 'icon' => 'tags'],
                'linv'   => ['label' => 'Investigations',   'route' => 'lab-investigations.index',           'table' => 'lab_investigations', 'icon' => 'flask'],
                'sym'    => ['label' => 'Symptoms',         'route' => 'symptoms.index',                     'table' => 'symptoms', 'icon' => 'thermometer'],
            ],
            'Blood Bank' => [
                'bg'     => ['label' => 'Blood Groups',     'route' => 'bb.blood-groups.index',  'table' => 'blood_groups',  'icon' => 'droplet'],
                'bcomp'  => ['label' => 'Components',       'route' => 'bb.components.index',    'table' => 'components',    'icon' => 'circle-half'],
                'bbag'   => ['label' => 'Blood Bags',       'route' => 'bb.blood-bags.index',    'table' => 'blood_bags',    'icon' => 'bag'],
                'bdef'   => ['label' => 'Deferral Reasons', 'route' => 'bb.deferral-reasons.index', 'table' => 'deferral_reasons', 'icon' => 'x-circle'],
            ],
            'Scheduling' => [
                'shift'  => ['label' => 'Shifts',           'route' => 'shifts.index',           'table' => 'shifts',            'icon' => 'clock-history'],
                'dshift' => ['label' => 'Doctor Shifts',    'route' => 'doctor-shifts.index',    'table' => 'doctor_shifts',     'icon' => 'calendar-week'],
                'dslot'  => ['label' => 'Doctor Slots',     'route' => 'doctor-slots.index',     'table' => 'doctor_slots',      'icon' => 'calendar-event'],
                'apri'   => ['label' => 'Appointment Priorities', 'route' => 'appointment-priorities.index', 'table' => 'appointment_priorities', 'icon' => 'flag'],
            ],
        ];
    }

    private function safeCount(?string $table): int
    {
        if (! $table) return 0;
        try {
            return DB::table($table)->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
