<?php
namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy\IpdIssue;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function index(Request $request)
    {
        $query = IpdIssue::with(['patient', 'ipdPatient', 'issuedBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('issue_no')) {
            $query->where('issue_no', 'like', '%' . $request->issue_no . '%');
        }

        if ($request->filled('patient_name')) {
            $query->whereHas('patient', fn($q) => $q->where('patient_name', 'like', '%' . $request->patient_name . '%'));
        }

        $issues = $query->latest()->paginate(15)->withQueryString();

        // Stats
        $pendingCount  = IpdIssue::where('status', 'pending')->count();
        $approvedCount = IpdIssue::where('status', 'approved')->count();
        $pendingValue  = IpdIssue::where('status', 'pending')->sum('total_amount');
        $totalCount    = IpdIssue::count();

        return view('pharmacy.approval.index', compact(
            'issues',
            'pendingCount',
            'approvedCount',
            'pendingValue',
            'totalCount'
        ));
    }

    public function export(Request $request)
    {
        $query = IpdIssue::with(['patient', 'ipdPatient', 'issuedBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('issue_no')) {
            $query->where('issue_no', 'like', '%' . $request->issue_no . '%');
        }
        if ($request->filled('patient_name')) {
            $query->whereHas('patient', fn($q) => $q->where('patient_name', 'like', '%' . $request->patient_name . '%'));
        }

        $issues   = $query->latest()->get();
        $filename = 'approvals_' . now()->format('Ymd_His') . '.csv';

        return response()->stream(function () use ($issues) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Issue No', 'Date', 'Ipd No', 'Patient', 'Ward/Bed', 'Req. No', 'Source', 'Drug Count', 'Amount (TK)', 'Status', 'Issued By']);

            foreach ($issues as $issue) {
                fputcsv($handle, [
                    $issue->issue_no,
                    $issue->created_at->format('d/m/Y'),
                    $issue->ipdPatient->ipd_no ?? '—',
                    $issue->patient->patient_name ?? '—',
                    $issue->ward_bed ?? '—',
                    $issue->requisition_no ?? '—',
                    $issue->request_source ?? '—',
                    $issue->drug_count,
                    number_format($issue->total_amount, 2),
                    ucfirst($issue->status),
                    $issue->issuedBy->name ?? '—',
                ]);
            }
            fclose($handle);
        }, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
