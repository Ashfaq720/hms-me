<?php

namespace Database\Seeders;

use App\Models\BedType;
use App\Models\Department;
use App\Models\Package;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RealPackageDataSeeder extends Seeder
{
    public function run(): void
    {
        $bedId = fn ($name) => BedType::where('name', $name)->value('id');
        $deptId = fn ($name) => Department::where('name', 'like', "%{$name}%")->value('id');

        $packages = [
            // ───── OPD packages (currently 0) ─────
            ['OPD Health Check-up — Basic',  'OPD-HC-BASIC', 'OPD', null, null, 3500,  10, 30, 'Consultation + CBC + Urine + ECG'],
            ['OPD Health Check-up — Standard','OPD-HC-STD',  'OPD', null, null, 7500,  15, 30, 'Specialist + 10 lab tests + CXR + ECG'],
            ['OPD Health Check-up — Premium','OPD-HC-PREM', 'OPD', null, null, 15000, 20, 30, 'Multi-specialist + 20 tests + USG + MRI option'],
            ['OPD Diabetic Care',            'OPD-DIAB',    'OPD', null, null, 4500,  10, 90, 'Endocrinologist + HbA1c + lipid + KFT'],
            ['OPD Cardiac Screening',        'OPD-CARD',    'OPD', null, 'Cardio', 6500, 10, 30, 'Cardiologist + ECG + Echo + Stress Test'],

            // ───── IPD packages ─────
            ['IPD Standard 3-Day Stay',      'IPD-STD-3D',  'IPD', 'General', null, 12000, 0, 5, 'General bed + nursing + basic meds for 3 days'],
            ['IPD Premium 5-Day Stay',       'IPD-PREM-5D', 'IPD', 'Cabin',   null, 35000, 5, 7, 'Cabin + specialist visits + meds for 5 days'],

            // ───── OT / Surgery packages ─────
            ['Appendectomy Package',          'OT-APPENDX',  'OT', 'General', null, 45000, 10, 4, 'OT + GA + bed 3 days + meds'],
            ['Laparoscopic Cholecystectomy',  'OT-LAPCHOLE', 'OT', 'Cabin',   null, 85000, 5,  4, 'Lap chole + cabin 3 days + meds'],
            ['Hernia Repair Package',         'OT-HERNIA',   'OT', 'General', null, 55000, 5,  3, 'Hernia repair + GA + bed 2 days'],
            ['Caesarean Section Package',     'OT-CSEC',     'OT', 'Cabin',   null, 65000, 0,  5, 'C-Section + cabin 4 days + meds'],

            // ───── ICU packages ─────
            ['ICU Standard (per day)',        'ICU-STD-1D',  'ICU', 'ICU', null, 12000, 0, 1, 'ICU bed + ICU nursing + monitor'],
            ['ICU Ventilator (per day)',      'ICU-VENT-1D', 'ICU', 'ICU', null, 18000, 0, 1, 'ICU bed + ventilator + monitor + nursing'],

            // ───── CCU packages ─────
            ['CCU Standard (per day)',        'CCU-STD-1D',  'CCU', 'CCU', null, 10000, 0, 1, 'CCU bed + cardiac monitor + nursing'],

            // ───── NICU packages ─────
            ['NICU Standard (per day)',       'NICU-STD-1D', 'NICU', 'NICU', null, 9000,  0, 1, 'NICU incubator + nursing + warmer'],
            ['NICU Premature Care (per day)', 'NICU-PREM',   'NICU', 'NICU', null, 13000, 0, 1, 'NICU + ventilator + 1:1 nursing'],

            // ───── Maternity packages ─────
            ['Normal Delivery Package',       'MAT-NVD',     'MATERNITY', 'General', null, 25000, 0, 3, 'NVD + bed 2 days + meds'],
            ['C-Section Maternity Package',   'MAT-CSEC',    'MATERNITY', 'Cabin',   null, 65000, 5, 5, 'LSCS + cabin 4 days + meds + newborn care'],

            // ───── Pathology packages ─────
            ['Basic Pathology Profile',       'PATH-BASIC',  'PATHOLOGY', null, null, 1800,  10, 14, 'CBC + FBS + Urine + Lipid'],
            ['Full Body Pathology Panel',     'PATH-FULL',   'PATHOLOGY', null, null, 5500,  15, 14, 'CBC + LFT + KFT + Lipid + TSH + HbA1c + Urine'],
            ['Pre-Op Pathology Workup',       'PATH-PREOP',  'PATHOLOGY', null, null, 3500,  10, 14, 'CBC + Coag + HIV + HBsAg + Glucose'],

            // ───── Radiology packages ─────
            ['Basic Radiology Screening',     'RAD-BASIC',   'RADIOLOGY', null, null, 2500,  10, 14, 'CXR + Abdomen USG'],
            ['Advanced Imaging Package',      'RAD-ADV',     'RADIOLOGY', null, null, 12000, 10, 14, 'CT Brain + Chest + USG'],

            // ───── Diagnostic packages ─────
            ['Diabetic Diagnostic Bundle',    'DIAG-DM',     'DIAGNOSTIC', null, null, 4500, 12, 30, 'HbA1c + FBS + PPBS + Lipid + KFT + Urine'],
            ['Cardiac Diagnostic Bundle',     'DIAG-CARDIO', 'DIAGNOSTIC', null, null, 7800, 12, 30, 'ECG + Echo + Stress Test + Lipid + Troponin'],
            ['General Health Diagnostic',     'DIAG-GEN',    'DIAGNOSTIC', null, null, 5500, 10, 30, 'Lipid + LFT + KFT + USG + CXR + ECG'],

            // ───── Pharmacy / Wellness ─────
            ['Monthly Diabetic Med Pack',     'PHRM-DIAB',   'PHARMACY', null, null, 1500, 5, 30, 'Metformin + Glimeperide + strips'],
            ['Hypertension Med Pack',         'PHRM-HTN',    'PHARMACY', null, null, 1200, 5, 30, 'Amlodipine + Telmisartan'],

            // Physio / Dental / Wellness
            ['Physiotherapy 10-Session Pack', 'PHYS-10',     'PHYSIOTHERAPY', null, null, 5000, 10, 30, '10 sessions @ 600'],
            ['Dental Cleaning + Whitening',   'DENT-CLEAN',  'DENTAL', null, null, 4500, 10, 30, 'Scaling + polishing + whitening'],
            ['Annual Corporate Health',       'CORP-ANNUAL', 'CORPORATE', null, null, 8500, 15, 365, 'Annual exec checkup'],
            ['Wellness — Yoga + Diet',        'WELL-YD',     'WELLNESS', null, null, 3500, 5, 30, '10 sessions yoga + diet plan'],
        ];

        foreach ($packages as [$name, $code, $type, $bed, $dept, $amt, $disc, $valid, $desc]) {
            Package::updateOrCreate(
                ['code' => $code],
                [
                    'name'           => $name,
                    'package_type'   => $type,
                    'admission_type' => 'ANY',
                    'department_id'  => $dept ? $deptId($dept) : null,
                    'bed_type_id'    => $bed ? $bedId($bed) : null,
                    'patient_type'   => 'ALL',
                    'validity_days'  => $valid,
                    'discount'       => $disc,
                    'total_amount'   => $amt,
                    'description'    => $desc,
                    'is_active'      => 1,
                    'status'         => 'active',
                ]
            );
        }
    }
}
