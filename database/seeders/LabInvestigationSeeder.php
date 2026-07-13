<?php

namespace Database\Seeders;

use App\Models\LabInvestigation;
use App\Models\LabInvestigationCategory;
use App\Models\LabInvestigationType;
use Illuminate\Database\Seeder;

/**
 * Lab Investigation master data.
 *
 * Structure: Type (Pathology / Radiology / ...) → Category (Haematology /
 * Biochemistry / X-Ray / ...) → Investigation (CBC, Urine RE, Chest X-Ray, ...).
 *
 * Idempotent — re-runs reuse existing rows by name.
 * Run: php artisan db:seed --class=LabInvestigationSeeder
 */
class LabInvestigationSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('▶ Seeding Lab Investigation Types…');
        $types = $this->seedTypes();

        $this->command->info('▶ Seeding Lab Investigation Categories…');
        $cats = $this->seedCategories($types);

        $this->command->info('▶ Seeding Lab Investigations…');
        $count = $this->seedInvestigations($cats);

        $this->command->info("✓ Lab master seeded: {$count} investigations across "
            . $cats->count() . ' categories, ' . $types->count() . ' types.');
    }

    protected function seedTypes()
    {
        // Names here MUST match the labels in the sidebar's $diagTypes array
        // (resources/views/backend/layouts/partials/sidebar.blade.php) so the
        // /pathology?type=… filter resolves to a real type row.
        $defs = [
            'Pathology',
            'Radiology',
            'Microbiology',
            'Histopathology',
            'Cytopathology',
            'Immunology / Serology',
            'Endocrinology',
            'Cardiology Diagnostics',
            'Genetics & Molecular',
            // Legacy entries kept for backward compatibility with older data:
            'Cardiology',
            'Endoscopy',
            'Other',
        ];
        $out = collect();
        foreach ($defs as $n) {
            $out->put($n, LabInvestigationType::firstOrCreate(['name' => $n], ['status' => 1]));
        }
        return $out;
    }

    protected function seedCategories($types)
    {
        // category => parent type
        $defs = [
            'Haematology'        => 'Pathology',
            'Biochemistry'       => 'Pathology',
            'Serology'           => 'Pathology',
            'Hormonal Assay'     => 'Pathology',
            'Urine & Stool'      => 'Pathology',
            'Clinical Chemistry' => 'Pathology',
            'Coagulation'        => 'Pathology',

            'X-Ray'              => 'Radiology',
            'Ultrasound'         => 'Radiology',
            'CT Scan'            => 'Radiology',
            'MRI'                => 'Radiology',
            'Mammography'        => 'Radiology',

            'ECG / Echo'         => 'Cardiology',
            'Stress Tests'       => 'Cardiology',
            'Holter'             => 'Cardiology',

            'Culture & Sensitivity' => 'Microbiology',
            'Smear & Stain'         => 'Microbiology',
            'Viral Markers'         => 'Microbiology',

            'Upper GI Endoscopy' => 'Endoscopy',
            'Colonoscopy'        => 'Endoscopy',
            'Bronchoscopy'       => 'Endoscopy',

            'Biopsy & Cytology'  => 'Histopathology',
            'FNAC'               => 'Histopathology',

            'Pulmonary Function' => 'Other',
            'Audiometry'         => 'Other',
        ];

        $out = collect();
        foreach ($defs as $name => $typeName) {
            $type = $types->get($typeName);
            if (! $type) continue;
            $out->put($name, LabInvestigationCategory::firstOrCreate(
                ['name' => $name],
                ['type_id' => $type->id, 'status' => 1]
            ));
        }
        return $out;
    }

    protected function seedInvestigations($cats): int
    {
        // [name, short_name, category, department, sample_type, report_hr, normal_range, unit, method, price]
        $defs = [
            // Haematology
            ['Complete Blood Count', 'CBC', 'Haematology', 'Pathology', 'Whole Blood (EDTA)', 4, '4.5-5.5 ×10⁶/µL (RBC), 4-11 ×10³/µL (WBC)', '×10⁶/µL', 'Automated Cell Counter', 350],
            ['ESR', 'ESR', 'Haematology', 'Pathology', 'Whole Blood (EDTA)', 2, '0-20 mm/hr', 'mm/hr', 'Westergren', 200],
            ['Peripheral Blood Film', 'PBF', 'Haematology', 'Pathology', 'Whole Blood (EDTA)', 6, 'No abnormal cells', '-', 'Microscopy', 450],
            ['Reticulocyte Count', 'RETIC', 'Haematology', 'Pathology', 'Whole Blood (EDTA)', 6, '0.5-2.5%', '%', 'Supravital Stain', 500],
            ['Haemoglobin', 'Hb', 'Haematology', 'Pathology', 'Whole Blood (EDTA)', 2, '12-18 g/dL', 'g/dL', 'Cyanmethaemoglobin', 150],

            // Biochemistry
            ['Fasting Blood Sugar', 'FBS', 'Biochemistry', 'Pathology', 'Serum', 2, '70-100 mg/dL', 'mg/dL', 'Glucose Oxidase', 200],
            ['Post-prandial Blood Sugar', 'PPBS', 'Biochemistry', 'Pathology', 'Serum', 2, '<140 mg/dL', 'mg/dL', 'Glucose Oxidase', 200],
            ['HbA1c', 'HbA1c', 'Biochemistry', 'Pathology', 'Whole Blood (EDTA)', 4, '<5.7% (normal)', '%', 'HPLC', 900],
            ['Lipid Profile', 'LIPID', 'Biochemistry', 'Pathology', 'Serum (fasting)', 4, 'TC <200, LDL <100, HDL >40, TG <150', 'mg/dL', 'Enzymatic', 1200],
            ['Liver Function Test', 'LFT', 'Biochemistry', 'Pathology', 'Serum', 4, 'ALT/AST 10-40 U/L, Bilirubin 0.2-1.2', 'U/L', 'Photometric', 1500],
            ['Renal Function Test', 'RFT', 'Biochemistry', 'Pathology', 'Serum', 4, 'Creatinine 0.6-1.3 mg/dL, Urea 15-40', 'mg/dL', 'Jaffe / Enzymatic', 1500],
            ['Electrolytes (Na/K/Cl)', 'ELEC', 'Biochemistry', 'Pathology', 'Serum', 4, 'Na 135-145, K 3.5-5.0', 'mmol/L', 'ISE', 800],
            ['Uric Acid', 'UA', 'Biochemistry', 'Pathology', 'Serum', 4, '3.4-7.0 mg/dL (M), 2.4-6.0 (F)', 'mg/dL', 'Uricase', 350],
            ['Calcium (Total)', 'Ca', 'Biochemistry', 'Pathology', 'Serum', 4, '8.5-10.5 mg/dL', 'mg/dL', 'Arsenazo III', 350],
            ['Bilirubin (Total/Direct)', 'BIL', 'Biochemistry', 'Pathology', 'Serum', 4, 'Total <1.2, Direct <0.3 mg/dL', 'mg/dL', 'Diazo', 400],
            ['CRP (Quantitative)', 'CRP', 'Biochemistry', 'Pathology', 'Serum', 4, '<6 mg/L', 'mg/L', 'Immunoturbidimetric', 600],

            // Serology
            ['ASO Titre', 'ASO', 'Serology', 'Pathology', 'Serum', 6, '<200 IU/mL', 'IU/mL', 'Latex Agglutination', 400],
            ['RA Factor', 'RA', 'Serology', 'Pathology', 'Serum', 6, 'Negative', '-', 'Latex Agglutination', 400],
            ['VDRL', 'VDRL', 'Serology', 'Pathology', 'Serum', 6, 'Non-reactive', '-', 'Flocculation', 350],
            ['HIV I & II Screening', 'HIV', 'Serology', 'Pathology', 'Serum', 24, 'Non-reactive', '-', 'ELISA', 900],
            ['HBsAg', 'HBsAg', 'Serology', 'Pathology', 'Serum', 24, 'Non-reactive', '-', 'ELISA', 600],
            ['Anti HCV', 'HCV', 'Serology', 'Pathology', 'Serum', 24, 'Non-reactive', '-', 'ELISA', 900],
            ['Dengue NS1', 'DEN-NS1', 'Serology', 'Pathology', 'Serum', 6, 'Negative', '-', 'ICT', 450],
            ['Dengue IgM/IgG', 'DEN-IGM', 'Serology', 'Pathology', 'Serum', 6, 'Negative', '-', 'ICT', 600],

            // Hormonal
            ['TSH', 'TSH', 'Hormonal Assay', 'Pathology', 'Serum', 12, '0.4-4.0 mIU/L', 'mIU/L', 'CLIA', 800],
            ['Free T3', 'FT3', 'Hormonal Assay', 'Pathology', 'Serum', 12, '2.0-4.4 pg/mL', 'pg/mL', 'CLIA', 800],
            ['Free T4', 'FT4', 'Hormonal Assay', 'Pathology', 'Serum', 12, '0.8-1.8 ng/dL', 'ng/dL', 'CLIA', 800],
            ['Beta hCG (Quant)', 'BHCG', 'Hormonal Assay', 'Pathology', 'Serum', 12, '<5 mIU/mL (non-pregnant)', 'mIU/mL', 'CLIA', 1200],
            ['PSA (Total)', 'PSA', 'Hormonal Assay', 'Pathology', 'Serum', 12, '<4 ng/mL', 'ng/mL', 'CLIA', 1500],
            ['Vitamin D (25-OH)', 'VITD', 'Hormonal Assay', 'Pathology', 'Serum', 24, '30-100 ng/mL', 'ng/mL', 'CLIA', 2200],
            ['Vitamin B12', 'B12', 'Hormonal Assay', 'Pathology', 'Serum', 12, '200-900 pg/mL', 'pg/mL', 'CLIA', 1500],

            // Urine & Stool
            ['Urine R/E', 'URINE-RE', 'Urine & Stool', 'Pathology', 'Random Urine', 4, 'Clear, pH 4.6-8.0, Negative for protein/sugar', '-', 'Dipstick + Microscopy', 250],
            ['Urine Culture & Sensitivity', 'UCS', 'Urine & Stool', 'Microbiology', 'Mid-stream Urine', 72, 'No growth', '-', 'Culture', 1200],
            ['24-Hour Urine Protein', '24U-PROT', 'Urine & Stool', 'Pathology', '24-hr Urine', 24, '<150 mg/day', 'mg/day', 'Turbidimetric', 800],
            ['Stool R/E', 'STOOL-RE', 'Urine & Stool', 'Pathology', 'Stool', 4, 'No ova, cyst or parasite', '-', 'Microscopy', 200],
            ['Stool Occult Blood', 'FOBT', 'Urine & Stool', 'Pathology', 'Stool', 4, 'Negative', '-', 'Immunochromatographic', 350],

            // Coagulation
            ['Prothrombin Time + INR', 'PT-INR', 'Coagulation', 'Pathology', 'Citrated Plasma', 4, '11-13 s, INR 0.9-1.1', 's', 'Photo-optical', 600],
            ['APTT', 'APTT', 'Coagulation', 'Pathology', 'Citrated Plasma', 4, '25-35 s', 's', 'Photo-optical', 500],
            ['D-Dimer', 'DDIM', 'Coagulation', 'Pathology', 'Citrated Plasma', 6, '<500 ng/mL', 'ng/mL', 'Immunoturbidimetric', 1500],

            // X-Ray
            ['Chest X-Ray PA View', 'CXR-PA', 'X-Ray', 'Radiology', 'Image', 2, 'Normal', '-', 'DR', 800],
            ['X-Ray KUB', 'XR-KUB', 'X-Ray', 'Radiology', 'Image', 2, 'Normal', '-', 'DR', 800],
            ['X-Ray Pelvis', 'XR-PEL', 'X-Ray', 'Radiology', 'Image', 2, 'Normal', '-', 'DR', 800],
            ['X-Ray Lumbar Spine', 'XR-LS', 'X-Ray', 'Radiology', 'Image', 2, 'Normal', '-', 'DR', 900],

            // Ultrasound
            ['USG Whole Abdomen', 'USG-ABD', 'Ultrasound', 'Radiology', 'Image', 4, 'Normal', '-', 'B-mode US', 2000],
            ['USG Pelvis', 'USG-PEL', 'Ultrasound', 'Radiology', 'Image', 4, 'Normal', '-', 'B-mode US', 1500],
            ['Obstetric USG', 'USG-OB', 'Ultrasound', 'Radiology', 'Image', 4, 'Normal', '-', 'B-mode US', 1800],
            ['Carotid Doppler', 'DOP-CAR', 'Ultrasound', 'Radiology', 'Image', 4, 'Normal', '-', 'Doppler US', 2500],

            // CT / MRI
            ['CT Brain (Plain)', 'CT-BR', 'CT Scan', 'Radiology', 'Image', 4, 'Normal', '-', 'Multi-slice CT', 5500],
            ['CT Chest (Plain)', 'CT-CH', 'CT Scan', 'Radiology', 'Image', 4, 'Normal', '-', 'Multi-slice CT', 6500],
            ['CT Abdomen + Contrast', 'CT-ABD', 'CT Scan', 'Radiology', 'Image', 6, 'Normal', '-', 'Multi-slice CT', 8500],
            ['MRI Brain', 'MRI-BR', 'MRI', 'Radiology', 'Image', 6, 'Normal', '-', '1.5T MRI', 9500],
            ['MRI Spine (LS)', 'MRI-LS', 'MRI', 'Radiology', 'Image', 6, 'Normal', '-', '1.5T MRI', 9500],
            ['Mammography (Bilateral)', 'MAM', 'Mammography', 'Radiology', 'Image', 4, 'BIRADS 1', '-', 'Digital Mammography', 3500],

            // Cardiology
            ['ECG (12-Lead)', 'ECG', 'ECG / Echo', 'Cardiology', 'Tracing', 1, 'Sinus rhythm', '-', '12-Lead ECG', 500],
            ['Echocardiogram', 'ECHO', 'ECG / Echo', 'Cardiology', 'Image', 2, 'Normal LV function', '-', '2D Echo', 2500],
            ['Treadmill Stress Test', 'TMT', 'Stress Tests', 'Cardiology', 'Tracing', 2, 'Negative for ischaemia', '-', 'Bruce Protocol', 3000],
            ['Holter ECG (24h)', 'HOLTER', 'Holter', 'Cardiology', 'Recording', 48, 'Normal', '-', 'Holter Recorder', 4500],

            // Microbiology
            ['Blood Culture & Sensitivity', 'BLD-CS', 'Culture & Sensitivity', 'Microbiology', 'Whole Blood', 72, 'No growth', '-', 'BACTEC', 1800],
            ['Sputum Culture', 'SP-CS', 'Culture & Sensitivity', 'Microbiology', 'Sputum', 72, 'Normal flora', '-', 'Culture', 1200],
            ['Sputum AFB', 'AFB', 'Smear & Stain', 'Microbiology', 'Sputum', 24, 'Negative', '-', 'Ziehl-Neelsen', 400],
            ['Gram Stain', 'GRAM', 'Smear & Stain', 'Microbiology', 'Various', 4, 'No organisms', '-', 'Gram Stain', 300],
            ['Hepatitis B DNA (PCR)', 'HBV-PCR', 'Viral Markers', 'Microbiology', 'Serum', 72, 'Not detected', 'copies/mL', 'Real-time PCR', 4500],

            // Endoscopy
            ['Upper GI Endoscopy', 'UGIE', 'Upper GI Endoscopy', 'Endoscopy', 'Image / Biopsy', 4, 'Normal', '-', 'Flexible Endoscopy', 5500],
            ['Colonoscopy', 'COL', 'Colonoscopy', 'Endoscopy', 'Image / Biopsy', 4, 'Normal', '-', 'Flexible Endoscopy', 8500],
            ['Bronchoscopy', 'BRONCH', 'Bronchoscopy', 'Endoscopy', 'Image / Biopsy', 4, 'Normal', '-', 'Flexible Bronchoscope', 9500],

            // Histopathology
            ['Biopsy — Small', 'BX-S', 'Biopsy & Cytology', 'Histopathology', 'Tissue', 96, 'Benign', '-', 'H&E Microscopy', 2500],
            ['Biopsy — Large', 'BX-L', 'Biopsy & Cytology', 'Histopathology', 'Tissue', 120, 'Benign', '-', 'H&E + IHC', 4500],
            ['FNAC', 'FNAC', 'FNAC', 'Histopathology', 'Aspirate', 24, 'Benign', '-', 'Pap Stain', 1200],
            ['Pap Smear', 'PAP', 'Biopsy & Cytology', 'Histopathology', 'Cervical Smear', 48, 'NIL-M', '-', 'Pap Stain', 800],

            // Other
            ['Spirometry / PFT', 'PFT', 'Pulmonary Function', 'Other', 'Breath Test', 2, 'FEV1/FVC >80%', '%', 'Spirometer', 1500],
            ['Pure Tone Audiometry', 'PTA', 'Audiometry', 'Other', 'Hearing Test', 1, '<25 dB HL', 'dB', 'Audiometer', 1200],
        ];

        $total = 0;
        $sort  = 1;
        foreach ($defs as [$name, $short, $catName, $dept, $sample, $hr, $range, $unit, $method, $price]) {
            $cat = $cats->get($catName);
            if (! $cat) continue;

            LabInvestigation::firstOrCreate(
                ['name' => $name],
                [
                    'short_name'        => $short,
                    'category_id'       => $cat->id,
                    'department'        => $dept,
                    'sample_type'       => $sample,
                    'report_time_hours' => $hr,
                    'normal_range'      => $range,
                    'unit'              => $unit,
                    'method'            => $method,
                    'price'             => $price,
                    'sort_order'        => $sort++,
                    'status'            => 1,
                ]
            );
            $total++;
        }
        return $total;
    }
}
