<?php

namespace App\Services;

use Exception;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Item;
use App\Models\PharmacySale;
use App\Models\PharmacySaleItem;
use App\Models\Invoice;
use App\Models\LabReport;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;

class ArrayExport implements FromArray, WithHeadings
{
    protected $data;
    protected $headings;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->headings = array_shift($this->data);
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return $this->headings;
    }
}

class CsvService extends BaseService
{
    public function exportPatients(array $filters = []): string
    {
        try {
            $query = Patient::with(["department", "assignedDoctor"]);

            if (isset($filters["department_id"])) {
                $query->where("department_id", $filters["department_id"]);
            }

            if (isset($filters["patient_type"])) {
                $query->where("patient_type", $filters["patient_type"]);
            }

            $patients = $query->orderBy("created_at", "desc")->get();

            $data = [];
            $data[] = [
                "Patient ID",
                "Full Name",
                "Email",
                "Phone",
                "Date of Birth",
                "Gender",
                "Address",
                "Patient Type",
                "Department",
                "Status",
                "Registration Date"
            ];

            foreach ($patients as $patient) {
                $data[] = [
                    $patient->patient_id ?? "PAT-" . $patient->id,
                    $patient->full_name,
                    $patient->email ?? "",
                    $patient->phone ?? "",
                    $patient->date_of_birth?->format("Y-m-d") ?? "",
                    ucfirst($patient->gender ?? ""),
                    $patient->address ?? "",
                    strtoupper($patient->patient_type ?? ""),
                    $patient->department->name ?? "",
                    ucfirst($patient->status ?? ""),
                    $patient->created_at->format("Y-m-d H:i:s")
                ];
            }

            $filename = "patients_export_" . date("Y-m-d_H-i-s") . ".csv";
            $filepath = storage_path("app/public/exports/" . $filename);

            $directory = dirname($filepath);
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            Excel::store(new ArrayExport($data), "public/exports/" . $filename, "public", \Maatwebsite\Excel\Excel::CSV);

            return $filepath;

        } catch (Exception $e) {
            throw new Exception("Failed to export patients data: " . $e->getMessage());
        }
    }

    public function exportDoctors(array $filters = []): string
    {
        try {
            $query = Doctor::with(["department", "user"]);

            if (isset($filters["department_id"])) {
                $query->where("department_id", $filters["department_id"]);
            }

            if (isset($filters["status"])) {
                $query->where("status", $filters["status"]);
            }

            $doctors = $query->orderBy("created_at", "desc")->get();

            $data = [];
            $data[] = [
                "Doctor ID",
                "Full Name",
                "Email",
                "Phone",
                "Specialization",
                "Department",
                "License Number",
                "Qualification",
                "Experience (Years)",
                "Status",
                "Registration Date"
            ];

            foreach ($doctors as $doctor) {
                $data[] = [
                    $doctor->doctor_id ?? "DOC-" . $doctor->id,
                    "Dr. " . $doctor->full_name,
                    $doctor->user->email ?? "",
                    $doctor->phone ?? "",
                    $doctor->specialization ?? "",
                    $doctor->department->name ?? "",
                    $doctor->license_number ?? "",
                    $doctor->qualification ?? "",
                    $doctor->experience_years ?? "",
                    ucfirst($doctor->status ?? ""),
                    $doctor->created_at->format("Y-m-d H:i:s")
                ];
            }

            $filename = "doctors_export_" . date("Y-m-d_H-i-s") . ".csv";
            $filepath = storage_path("app/public/exports/" . $filename);

            $directory = dirname($filepath);
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            Excel::store(new ArrayExport($data), "public/exports/" . $filename, "public", \Maatwebsite\Excel\Excel::CSV);

            return $filepath;

        } catch (Exception $e) {
            throw new Exception("Failed to export doctors data: " . $e->getMessage());
        }
    }

    public function exportAppointments(string $startDate, string $endDate, array $filters = []): string
    {
        try {
            $query = Appointment::with(["patient", "doctor", "department"]);

            $query->whereBetween("appointment_date", [$startDate, $endDate]);

            if (isset($filters["department_id"])) {
                $query->where("department_id", $filters["department_id"]);
            }

            if (isset($filters["doctor_id"])) {
                $query->where("doctor_id", $filters["doctor_id"]);
            }

            if (isset($filters["status"])) {
                $query->where("status", $filters["status"]);
            }

            $appointments = $query->orderBy("appointment_date", "desc")->get();

            $data = [];
            $data[] = [
                "Appointment ID",
                "Appointment Date",
                "Start Time",
                "End Time",
                "Patient Name",
                "Patient ID",
                "Doctor Name",
                "Department",
                "Type",
                "Status",
                "Symptoms",
                "Notes",
                "Created At"
            ];

            foreach ($appointments as $appointment) {
                $data[] = [
                    $appointment->appointment_id ?? "APT-" . $appointment->id,
                    $appointment->appointment_date->format("Y-m-d"),
                    $appointment->start_time ?? "",
                    $appointment->end_time ?? "",
                    $appointment->patient->full_name ?? "",
                    $appointment->patient->patient_id ?? "",
                    "Dr. " . $appointment->doctor->full_name ?? "",
                    $appointment->department->name ?? "",
                    ucfirst($appointment->appointment_type ?? ""),
                    ucfirst($appointment->status ?? ""),
                    $appointment->symptoms ?? "",
                    $appointment->notes ?? "",
                    $appointment->created_at->format("Y-m-d H:i:s")
                ];
            }

            $filename = "appointments_export_" . $startDate . "_to_" . $endDate . "_" . date("Y-m-d_H-i-s") . ".csv";
            $filepath = storage_path("app/public/exports/" . $filename);

            $directory = dirname($filepath);
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            Excel::store(new ArrayExport($data), "public/exports/" . $filename, "public", \Maatwebsite\Excel\Excel::CSV);

            return $filepath;

        } catch (Exception $e) {
            throw new Exception("Failed to export appointments data: " . $e->getMessage());
        }
    }

    public function exportPharmacySales(string $startDate, string $endDate, array $filters = []): string
    {
        try {
            $query = PharmacySale::with(["patient", "cashier", "items.item"]);

            $query->whereBetween("sale_date", [$startDate, $endDate]);

            if (isset($filters["patient_type"])) {
                $query->where("patient_type", $filters["patient_type"]);
            }

            if (isset($filters["payment_status"])) {
                $query->where("payment_status", $filters["payment_status"]);
            }

            $sales = $query->orderBy("sale_date", "desc")->get();

            $data = [];
            $data[] = [
                "Sale Number",
                "Sale Date",
                "Sale Time",
                "Patient Name",
                "Patient ID",
                "Patient Type",
                "Cashier",
                "Subtotal Amount",
                "Tax Amount",
                "Discount Amount",
                "Total Amount",
                "Paid Amount",
                "Outstanding Amount",
                "Payment Status",
                "Sale Type",
                "Items Count",
                "Created At"
            ];

            foreach ($sales as $sale) {
                $data[] = [
                    $sale->sale_number,
                    $sale->sale_date->format("Y-m-d"),
                    $sale->sale_time?->format("H:i:s") ?? "",
                    $sale->patient->full_name ?? "Walk-in Patient",
                    $sale->patient->patient_id ?? "",
                    strtoupper($sale->patient_type ?? ""),
                    $sale->cashier->name ?? "",
                    (float) $sale->subtotal_amount,
                    (float) $sale->tax_amount,
                    (float) $sale->discount_amount,
                    (float) $sale->total_amount,
                    (float) $sale->paid_amount,
                    (float) $sale->outstanding_amount,
                    ucfirst($sale->payment_status ?? ""),
                    ucfirst($sale->sale_type ?? ""),
                    $sale->items->count(),
                    $sale->created_at->format("Y-m-d H:i:s")
                ];
            }

            $filename = "pharmacy_sales_export_" . $startDate . "_to_" . $endDate . "_" . date("Y-m-d_H-i-s") . ".csv";
            $filepath = storage_path("app/public/exports/" . $filename);

            $directory = dirname($filepath);
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            Excel::store(new ArrayExport($data), "public/exports/" . $filename, "public", \Maatwebsite\Excel\Excel::CSV);

            return $filepath;

        } catch (Exception $e) {
            throw new Exception("Failed to export pharmacy sales data: " . $e->getMessage());
        }
    }
    public function exportMedicalItems(array $filters = []): string
    {
        try {
            $query = Item::with(["generic", "manufacturer"]);

            if (isset($filters["generic_id"])) {
                $query->where("generic_id", $filters["generic_id"]);
            }

            if (isset($filters["manufacturer_id"])) {
                $query->where("manufacturer_id", $filters["manufacturer_id"]);
            }

            if (isset($filters["status"])) {
                if ($filters["status"] === "active") {
                    $query->where("is_active", true);
                } elseif ($filters["status"] === "inactive") {
                    $query->where("is_active", false);
                }
            }

            $items = $query->orderBy("name")->get();

            $data = [];
            $data[] = [
                "Item Code",
                "Name",
                "Generic Name",
                "Manufacturer",
                "Description",
                "Cost Price",
                "Selling Price",
                "MRP",
                "Current Stock",
                "Min Stock Level",
                "Max Stock Level",
                "Unit",
                "Batch Details",
                "Is Active",
                "Created At"
            ];

            foreach ($items as $item) {
                $data[] = [
                    $item->item_code ?? "",
                    $item->name,
                    $item->generic->name ?? "",
                    $item->manufacturer->name ?? "",
                    $item->description ?? "",
                    (float) $item->cost_price,
                    (float) $item->selling_price,
                    (float) $item->mrp,
                    (int) $item->current_stock,
                    (int) $item->min_stock_level,
                    (int) $item->max_stock_level,
                    $item->unit,
                    json_encode($item->batch_details) ?? "",
                    $item->is_active ? "Yes" : "No",
                    $item->created_at->format("Y-m-d H:i:s")
                ];
            }

            $filename = "medical_items_export_" . date("Y-m-d_H-i-s") . ".csv";
            $filepath = storage_path("app/public/exports/" . $filename);

            $directory = dirname($filepath);
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            Excel::store(new ArrayExport($data), "public/exports/" . $filename, "public", \Maatwebsite\Excel\Excel::CSV);

            return $filepath;

        } catch (Exception $e) {
            throw new Exception("Failed to export medical items data: " . $e->getMessage());
        }
    }

    public function exportInvoices(string $startDate, string $endDate, array $filters = []): string
    {
        try {
            $query = Invoice::with(["patient", "doctor", "department"]);

            $query->whereBetween("invoice_date", [$startDate, $endDate]);

            if (isset($filters["department_id"])) {
                $query->where("department_id", $filters["department_id"]);
            }

            if (isset($filters["doctor_id"])) {
                $query->where("doctor_id", $filters["doctor_id"]);
            }

            if (isset($filters["status"])) {
                $query->where("status", $filters["status"]);
            }

            $invoices = $query->orderBy("invoice_date", "desc")->get();

            $data = [];
            $data[] = [
                "Invoice Number",
                "Invoice Date",
                "Patient Name",
                "Patient ID",
                "Doctor Name",
                "Department",
                "Subtotal",
                "Tax Amount",
                "Discount Amount",
                "Total Amount",
                "Paid Amount",
                "Outstanding Amount",
                "Status",
                "Payment Method",
                "Created At"
            ];

            foreach ($invoices as $invoice) {
                $data[] = [
                    $invoice->invoice_number,
                    $invoice->invoice_date->format("Y-m-d"),
                    $invoice->patient->full_name ?? "",
                    $invoice->patient->patient_id ?? "",
                    "Dr. " . $invoice->doctor->full_name ?? "",
                    $invoice->department->name ?? "",
                    (float) $invoice->subtotal,
                    (float) $invoice->tax_amount,
                    (float) $invoice->discount_amount,
                    (float) $invoice->total_amount,
                    (float) $invoice->paid_amount,
                    (float) $invoice->outstanding_amount,
                    ucfirst($invoice->status ?? ""),
                    $invoice->payment_method ?? "",
                    $invoice->created_at->format("Y-m-d H:i:s")
                ];
            }

            $filename = "invoices_export_" . $startDate . "_to_" . $endDate . "_" . date("Y-m-d_H-i-s") . ".csv";
            $filepath = storage_path("app/public/exports/" . $filename);

            $directory = dirname($filepath);
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            Excel::store(new ArrayExport($data), "public/exports/" . $filename, "public", \Maatwebsite\Excel\Excel::CSV);

            return $filepath;

        } catch (Exception $e) {
            throw new Exception("Failed to export invoices data: " . $e->getMessage());
        }
    }

    public function exportLabReports(string $startDate, string $endDate, array $filters = []): string
    {
        try {
            $query = LabReport::with(["patient", "doctor", "department"]);

            $query->whereBetween("report_date", [$startDate, $endDate]);

            if (isset($filters["department_id"])) {
                $query->where("department_id", $filters["department_id"]);
            }

            if (isset($filters["doctor_id"])) {
                $query->where("doctor_id", $filters["doctor_id"]);
            }

            if (isset($filters["status"])) {
                $query->where("status", $filters["status"]);
            }

            $reports = $query->orderBy("report_date", "desc")->get();

            $data = [];
            $data[] = [
                "Report Number",
                "Report Date",
                "Patient Name",
                "Patient ID",
                "Doctor Name",
                "Department",
                "Test Name",
                "Test Category",
                "Result",
                "Reference Range",
                "Units",
                "Status",
                "Notes",
                "Created At"
            ];

            foreach ($reports as $report) {
                $data[] = [
                    $report->report_number,
                    $report->report_date->format("Y-m-d"),
                    $report->patient->full_name ?? "",
                    $report->patient->patient_id ?? "",
                    "Dr. " . $report->doctor->full_name ?? "",
                    $report->department->name ?? "",
                    $report->test_name ?? "",
                    $report->test_category ?? "",
                    $report->result ?? "",
                    $report->reference_range ?? "",
                    $report->units ?? "",
                    ucfirst($report->status ?? ""),
                    $report->notes ?? "",
                    $report->created_at->format("Y-m-d H:i:s")
                ];
            }

            $filename = "lab_reports_export_" . $startDate . "_to_" . $endDate . "_" . date("Y-m-d_H-i-s") . ".csv";
            $filepath = storage_path("app/public/exports/" . $filename);

            $directory = dirname($filepath);
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            Excel::store(new ArrayExport($data), "public/exports/" . $filename, "public", \Maatwebsite\Excel\Excel::CSV);

            return $filepath;

        } catch (Exception $e) {
            throw new Exception("Failed to export lab reports data: " . $e->getMessage());
        }
    }

    public function importPatients(string $filepath): array
    {
        try {
            if (!file_exists($filepath)) {
                throw new Exception("Import file not found");
            }

            $handle = fopen($filepath, "r");
            if ($handle === false) {
                throw new Exception("Unable to open import file");
            }

            $imported = 0;
            $failed = 0;
            $errors = [];
            $header = null;
            $rowNumber = 0;

            while (($row = fgetcsv($handle, 1000, ",")) !== false) {
                $rowNumber++;

                if (empty(array_filter($row))) {
                    continue;
                }

                if ($header === null) {
                    $header = $row;
                    continue;
                }

                $record = array_combine($header, $row);
                if ($record === false) {
                    $failed++;
                    $errors[] = "Row {$rowNumber}: Invalid CSV format";
                    continue;
                }

                try {
                    if (empty($record["Full Name"])) {
                        throw new Exception("Full Name is required");
                    }

                    $existingPatient = null;
                    if (!empty($record["Email"])) {
                        $existingPatient = Patient::where("email", $record["Email"])->first();
                    }
                    if (!$existingPatient && !empty($record["Phone"])) {
                        $existingPatient = Patient::where("phone", $record["Phone"])->first();
                    }

                    if ($existingPatient) {
                        throw new Exception("Patient with this email or phone already exists");
                    }

                    $nameParts = explode(" ", $record["Full Name"], 2);
                    $firstName = $nameParts[0] ?? "";
                    $lastName = $nameParts[1] ?? "";

                    $department = null;
                    if (!empty($record["Department"])) {
                        $department = \App\Models\Department::where("name", $record["Department"])->first();
                    }

                    Patient::create([
                        "first_name" => $firstName,
                        "last_name" => $lastName,
                        "email" => $record["Email"] ?? null,
                        "phone" => $record["Phone"] ?? null,
                        "date_of_birth" => !empty($record["Date of Birth"]) ? $record["Date of Birth"] : null,
                        "gender" => strtolower($record["Gender"] ?? ""),
                        "address" => $record["Address"] ?? null,
                        "patient_type" => strtolower($record["Patient Type"] ?? "opd"),
                        "department_id" => $department?->id,
                        "status" => strtolower($record["Status"] ?? "active"),
                    ]);

                    $imported++;

                } catch (Exception $e) {
                    $failed++;
                    $errors[] = "Row {$rowNumber}: " . $e->getMessage();
                }
            }

            fclose($handle);

            return [
                "imported" => $imported,
                "failed" => $failed,
                "errors" => $errors
            ];

        } catch (Exception $e) {
            throw new Exception("Failed to import patients: " . $e->getMessage());
        }
    }
}
