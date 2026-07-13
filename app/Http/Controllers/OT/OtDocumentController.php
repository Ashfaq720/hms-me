<?php

namespace App\Http\Controllers\OT;

use App\Http\Controllers\Controller;
use App\Models\Ot\OtAuditLog;
use App\Models\Ot\OtDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OtDocumentController extends OtBaseController
{
    public function index(Request $request)
    {
        $this->gate('ot_documents_access');
        $documents = OtDocument::with(['surgeryRequest.patient', 'schedule'])
            ->latest()
            ->paginate(30);

        return view('ot.documents.index', compact('documents'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'surgery_request_id' => 'nullable|exists:ot_surgery_requests,id',
            'surgery_schedule_id' => 'nullable|exists:ot_surgery_schedules,id',
            'document_type' => 'required|in:consent,pre_op,intra_op,post_op,discharge,other',
            'title' => 'required|string|max:255',
            'file' => 'required|file|max:10240',
            'notes' => 'nullable|string',
        ]);

        $path = $request->file('file')->store('ot-documents', 'public');

        $document = OtDocument::create([
            'surgery_request_id' => $data['surgery_request_id'] ?? null,
            'surgery_schedule_id' => $data['surgery_schedule_id'] ?? null,
            'document_type' => $data['document_type'],
            'title' => $data['title'],
            'file_path' => $path,
            'mime_type' => $request->file('file')->getMimeType(),
            'uploaded_by' => auth()->id(),
            'notes' => $data['notes'] ?? null,
        ]);

        OtAuditLog::record('ot_document', $document->id, 'uploaded');

        return back()->with('success', 'Document uploaded.');
    }

    public function download($id)
    {
        $document = OtDocument::findOrFail($id);

        if (! $document->file_path || ! Storage::disk('public')->exists($document->file_path)) {
            abort(404);
        }

        return Storage::disk('public')->download($document->file_path, $document->title);
    }

    public function destroy($id)
    {
        $document = OtDocument::findOrFail($id);

        if ($document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }
        $document->delete();

        OtAuditLog::record('ot_document', $document->id, 'deleted');

        return back()->with('success', 'Document deleted.');
    }

    public function sign($id)
    {
        $document = OtDocument::findOrFail($id);
        $document->update([
            'is_signed' => true,
            'signed_at' => now(),
        ]);

        OtAuditLog::record('ot_document', $document->id, 'signed');

        return back()->with('success', 'Document marked as signed.');
    }
}
