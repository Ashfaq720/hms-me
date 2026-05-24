<?php
namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use App\Models\IpdPatient;
use App\Models\Pharmacy\IpdIssue;
use App\Models\Pharmacy\IpdIssueItem;
use App\Models\Pharmacy\Medicine;
use App\Models\Pharmacy\MedicineBatch;
use App\Models\Prescription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class IpdIssueController extends Controller
{
    public function index(Request $request)
    {
        $query = IpdIssue::with(['ipdPatient', 'patient', 'issuedBy'])
            ->latest();

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('patient_name')) {
            $query->whereHas('patient', fn($q) => $q->where('patient_name', 'like', '%' . $request->patient_name . '%'));
        }

        if ($request->filled('requisition_no')) {
            $query->where('requisition_no', 'like', '%' . $request->requisition_no . '%');
        }

        if ($request->filled('issue_no')) {
            $query->where('issue_no', 'like', '%' . $request->issue_no . '%');
        }

        if ($request->filled('request_source')) {
            $query->where('request_source', $request->request_source);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $issues = $query->get();

        // Stats
        $totalAmount       = IpdIssue::sum('total_amount');
        $pendingCount      = IpdIssue::where('status', 'pending')->count();
        $recentIssuedCount = IpdIssue::where('status', 'approved')
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->distinct('patient_id')
            ->count('patient_id');
        $toBeBilled = IpdIssue::where('status', 'approved')->sum('total_amount');

        // Filter options
        $requestSources = IpdIssue::distinct()->pluck('request_source')->filter()->sort()->values();

        return view('pharmacy.ipd-issue.index', compact(
            'issues',
            'totalAmount',
            'pendingCount',
            'recentIssuedCount',
            'toBeBilled',
            'requestSources'
        ));
    }

    public function create()
    {
        // Auto-generate Ipd Issue No
        $lastId  = IpdIssue::max('id') ?? 0;
        $issueNo = 'Ipd-' . str_pad($lastId + 1, 6, '0', STR_PAD_LEFT);

        // Auto-generate Requisition No
        $lastReq       = IpdIssue::max('id') ?? 0;
        $requisitionNo = 'Auto-MR-' . str_pad($lastReq + 1, 5, '0', STR_PAD_LEFT);

        $ipdPatients = IpdPatient::with('patient')
            ->where('status', 'Admitted')
            ->get();

        $beds      = Bed::orderBy('name')->get();
        $users     = User::orderBy('name')->get();
        $medicines = Medicine::where('status', 1)->orderBy('medicine_name')->get();

        $prescriptions = Prescription::whereNotNull('ipd_patient_id')
            ->orderByDesc('id')
            ->get();

        $stores = MedicineBatch::where('status', 1)
            ->distinct()
            ->pluck('store')
            ->filter()
            ->sort()
            ->values();

        return view('pharmacy.ipd-issue.create', compact(
            'issueNo',
            'requisitionNo',
            'ipdPatients',
            'beds',
            'users',
            'medicines',
            'prescriptions',
            'stores'
        ));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ipd_patient_id'       => 'required|exists:i_p_d_patients,id',
            'ward_bed'             => 'required|string',
            'requisition_no'       => 'required|string',
            'request_source'       => 'required|string',
            'request_date'         => 'required|date',
            'items'                => 'required|array|min:1',
            'items.*.medicine_id'  => 'required|exists:medicines,id',
            'items.*.duration'     => 'required|string',
            'items.*.qty_required' => 'required|integer|min:1',
            'items.*.store'        => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.pharmacy.ipd-issue')
                ->withErrors($validator)
                ->withInput();
        }

        $ipdPatient = IpdPatient::findOrFail($request->ipd_patient_id);

        // Generate issue no
        $lastId  = IpdIssue::max('id') ?? 0;
        $issueNo = 'IpdISU-' . str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);

        DB::transaction(function () use ($request, $ipdPatient, $issueNo) {
            $issue = IpdIssue::create([
                'issue_no'       => $issueNo,
                'ipd_patient_id' => $ipdPatient->id,
                'patient_id'     => $ipdPatient->patient_id,
                'requisition_no' => $request->requisition_no,
                'ward_bed'       => $request->ward_bed,
                'request_source' => $request->request_source,
                'issued_by'      => auth()->id(),
                'drug_count'     => count($request->items),
                'total_amount'   => 0,
                'status'         => 'pending',
                'note'           => $request->remarks,
            ]);

            $totalAmount = 0;

            foreach ($request->items as $item) {
                $medicine     = Medicine::find($item['medicine_id']);
                $availableQty = $medicine->available_qty ?? 0;

                IpdIssueItem::create([
                    'ipd_issue_id'  => $issue->id,
                    'medicine_id'   => $item['medicine_id'],
                    'duration'      => $item['duration'],
                    'qty_required'  => $item['qty_required'],
                    'available_qty' => $availableQty,
                    'store'         => $item['store'],
                ]);

                // Calculate amount from batch selling price
                $batch = MedicineBatch::where('medicine_id', $item['medicine_id'])
                    ->where('store', $item['store'])
                    ->where('quantity', '>', 0)
                    ->orderBy('expiry_date')
                    ->first();

                $price        = $batch->selling_price ?? 0;
                $totalAmount += $price * $item['qty_required'];
            }

            $issue->update(['total_amount' => $totalAmount]);
        });

        return redirect()->route('admin.pharmacy.ipd-issue')
            ->with('success', 'Ipd Issue created successfully.');
    }

    public function show($id)
    {
        $issue = IpdIssue::with([
            'ipdPatient.patient',
            'patient',
            'issuedBy',
            'items.medicine',
        ])->findOrFail($id);

        return view('pharmacy.ipd-issue.show', compact('issue'));
    }

    public function approve($id)
    {
        $issue = IpdIssue::findOrFail($id);

        if ($issue->status !== 'pending') {
            return redirect()->route('admin.pharmacy.ipd-issue')
                ->with('error', 'Only pending issues can be approved.');
        }

        $issue->update(['status' => 'approved']);

        return redirect()->route('admin.pharmacy.ipd-issue')
            ->with('success', "Issue {$issue->issue_no} approved successfully.");
    }

    public function printSingle($id)
    {
        $issue = IpdIssue::with([
            'ipdPatient',
            'patient',
            'issuedBy',
            'items.medicine',
        ])->findOrFail($id);

        return view('pharmacy.ipd-issue.print-single', compact('issue'));
    }

    public function export(Request $request)
    {
        $issues = $this->buildFilteredQuery($request)
            ->with(['ipdPatient', 'patient', 'issuedBy'])
            ->get();

        $filename = 'ipd-issues-' . now()->format('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($issues) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Issue No', 'Ipd No', 'Patient', 'Ward/Bed',
                'Requisition No', 'Request Source', 'Drug Count',
                'Total Amount (TK)', 'Status', 'Issued By', 'Date',
            ]);

            foreach ($issues as $i) {
                fputcsv($handle, [
                    $i->issue_no,
                    $i->ipdPatient->ipd_no ?? '',
                    $i->patient->patient_name ?? '',
                    $i->ward_bed ?? '',
                    $i->requisition_no ?? '',
                    $i->request_source ?? '',
                    $i->drug_count,
                    number_format($i->total_amount, 2),
                    $i->status,
                    $i->issuedBy->name ?? '',
                    $i->created_at->format('d/m/Y'),
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function printList(Request $request)
    {
        $issues = $this->buildFilteredQuery($request)
            ->with(['ipdPatient', 'patient', 'issuedBy', 'items.medicine'])
            ->get();

        $filters = $request->only([
            'date_from', 'patient_name', 'issue_no',
            'requisition_no', 'request_source', 'status',
        ]);

        return view('pharmacy.ipd-issue.print', compact('issues', 'filters'));
    }

    private function buildFilteredQuery(Request $request)
    {
        $query = IpdIssue::latest();

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('patient_name')) {
            $query->whereHas('patient', fn($q) => $q->where('patient_name', 'like', '%' . $request->patient_name . '%'));
        }
        if ($request->filled('requisition_no')) {
            $query->where('requisition_no', 'like', '%' . $request->requisition_no . '%');
        }
        if ($request->filled('issue_no')) {
            $query->where('issue_no', 'like', '%' . $request->issue_no . '%');
        }
        if ($request->filled('request_source')) {
            $query->where('request_source', $request->request_source);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query;
    }

    public function getMedicineQty(Medicine $medicine)
    {
        $totalBatchQty = MedicineBatch::where('medicine_id', $medicine->id)
            ->where('status', 1)
            ->where('quantity', '>', 0)
            ->sum('quantity');

        return response()->json([
            'available_qty' => $totalBatchQty ?: ($medicine->available_qty ?? 0),
        ]);
    }
}
