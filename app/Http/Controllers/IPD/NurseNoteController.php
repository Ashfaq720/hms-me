<?php
namespace App\Http\Controllers\IPD;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Ipd\IpdNurseNote;
use App\Models\Ipd\IpdNurseNoteReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NurseNoteController extends Controller
{
    public function create($id)
    {
        $doctors = Doctor::select('id', 'name')->orderBy('name')->get();
        return view('ipd_patients.nurse-note.create', compact('id', 'doctors'));
    }

    public function store(Request $request, $id)
    {
        $validated = $request->validate([
            'title'           => 'nullable|string|max:255',
            'doctor_category' => 'nullable|string|max:255',
            'shift'           => 'nullable|string|max:255',
            'doctor_id'       => 'nullable|exists:doctors,id',
            'priority'        => 'required|in:Normal,Urgent,Critical',
            'file'            => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
            'date'            => 'required|date',
            'nurse_name'      => 'required|string|max:255',
            'note'            => 'required|string',
            'observations'    => 'nullable|string',
        ]);

        try {
            $validated['ipd_patient_id'] = $id;

            if ($request->hasFile('file')) {
                $validated['file'] = $request->file('file')->store('ipd/nurse-notes', 'public');
            }

            IpdNurseNote::create($validated);

            // return redirect()->route('ipd-patients.ipd-patients.show', $id)
            //     ->with('success', 'Nurse note saved successfully.');

            return redirect(route('ipd-patients.ipd-patients.show', $id) . '?tab=nurse')
                ->with('success', 'Nurse note saved successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Failed to save nurse note: ' . $e->getMessage());
        }
    }

    public function reply(Request $request, $noteId)
    {
        $request->validate([
            'reply' => 'required|string',
        ]);

        $note = IpdNurseNote::findOrFail($noteId);

        IpdNurseNoteReply::create([
            'ipd_nurse_note_id' => $note->id,
            'user_id'           => Auth::id(),
            'user_name'         => Auth::user()->name ?? 'User',
            'user_role'         => Auth::user()?->getRoleNames()->first(),
            'reply'             => $request->reply,
        ]);

        // return redirect()->route('ipd-patients.ipd-patients.show', $note->ipd_patient_id)
        //     ->with('success', 'Reply added.');

        return redirect(route('ipd-patients.ipd-patients.show', $note->ipd_patient_id) . '?tab=nurse')
            ->with('success', 'Reply added.');
    }
}
