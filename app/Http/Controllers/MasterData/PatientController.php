<?php
namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\PatientRequest;
use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PatientController extends Controller
{
    public function index()
    {
        $stats = [
            'total'    => Patient::count(),
            'active'   => Patient::where('is_active', true)->count(),
            'ipd'      => Patient::where('is_ipd', true)->count(),
            'deceased' => Patient::where('is_dead', true)->count(),
        ];

        $patients = Patient::latest()->paginate(20);

        return view('patients.index', compact('stats', 'patients'));
    }

    public function datatable()
    {
        $draw   = (int) request('draw', 1);
        $start  = (int) request('start', 0);
        $length = (int) request('length', 15);
        $search = request('search.value', '');

        $query = Patient::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('patient_name', 'like', "%{$search}%")
                  ->orWhere('mrn', 'like', "%{$search}%")
                  ->orWhere('health_card_no', 'like', "%{$search}%")
                  ->orWhere('mobileno', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $total    = Patient::count();
        $filtered = $query->count();

        $orderColIndex = (int) request('order.0.column', 0);
        $orderDir      = request('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $sortable      = [0 => 'id', 1 => 'patient_name'];
        $query->orderBy($sortable[$orderColIndex] ?? 'id', $orderDir);

        /** @var \Illuminate\Database\Eloquent\Collection<int, Patient> $patients */
        $patients = $query->skip($start)->take($length)->get();

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $patients->map(fn (Patient $p) => $this->dtRow($p)),
        ]);
    }

    private function dtRow(Patient $p): array
    {
        // Avatar
        $words    = array_filter(explode(' ', $p->patient_name));
        $initials = strtoupper(
            substr($words[array_key_first($words)] ?? '', 0, 1) .
            substr($words[array_key_last($words)]  ?? '', 0, 1)
        );
        $palette = ['#4361ee', '#3a86ff', '#06d6a0', '#8338ec', '#fb5607', '#e07a5f', '#3d405b', '#2d6a4f'];
        $bgColor = $palette[abs(crc32($p->patient_name)) % count($palette)];

        if ($p->image) {
            $avatar = '<img src="' . asset('storage/' . $p->image) . '" width="40" height="40" class="rounded-2 flex-shrink-0" style="object-fit:cover;">';
        } else {
            $avatar = '<div class="rounded-2 flex-shrink-0 d-flex align-items-center justify-content-center text-white fw-bold" style="width:40px;height:40px;background:' . $bgColor . ';font-size:13px;letter-spacing:.5px">' . e($initials) . '</div>';
        }

        $hc      = $p->health_card_no ? ' &middot; ' . e($p->health_card_no) : '';
        $patient = '<div class="d-flex align-items-center gap-2">' . $avatar
            . '<div><div class="fw-semibold lh-sm">' . e($p->patient_name) . '</div>'
            . '<div class="text-muted" style="font-size:11.5px;line-height:1.6">' . e($p->mrn) . $hc . '</div></div></div>';

        // Contact
        $contact = '';
        if ($p->mobileno) {
            $contact .= '<div><i class="fa-solid fa-phone fa-xs text-muted me-1"></i>' . e($p->mobileno) . '</div>';
        }
        if ($p->email) {
            $contact .= '<div class="text-muted small mt-1"><i class="fa-solid fa-envelope fa-xs me-1"></i>' . e($p->email) . '</div>';
        }

        // Demographics
        $demo = '<div class="d-flex flex-wrap gap-1 align-items-center">';
        if ($p->gender) {
            $g = strtolower($p->gender);
            if ($g === 'male') {
                $demo .= '<span class="badge bg-info"><i class="fa-solid fa-mars me-1"></i>Male</span>';
            } elseif ($g === 'female') {
                $demo .= '<span class="badge" style="background:#c2185b"><i class="fa-solid fa-venus me-1"></i>Female</span>';
            } else {
                $demo .= '<span class="badge bg-secondary">' . e($p->gender) . '</span>';
            }
        }
        if ($p->blood_group) {
            $demo .= '<span class="badge bg-danger">' . e($p->blood_group) . '</span>';
        }
        $demo .= '</div>';
        if ($p->dob) {
            $demo .= '<div class="text-muted mt-1" style="font-size:11.5px">' . $p->dob->format('d M Y') . ' &middot; ' . $p->dob->age . 'y</div>';
        }

        // Status
        $status  = '<div class="d-flex flex-wrap gap-1">';
        $status .= $p->is_active
            ? '<span class="badge bg-success">Active</span>'
            : '<span class="badge bg-secondary">Inactive</span>';
        if ($p->is_ipd)  $status .= '<span class="badge bg-warning text-dark">IPD</span>';
        if ($p->is_dead) $status .= '<span class="badge bg-dark">Deceased</span>';
        $status .= '</div>';

        // Action
        $showUrl   = route('patients.show', $p);
        $editUrl   = route('patients.edit', $p);
        $hcUrl     = route('health-card.show', $p);
        $deleteUrl = route('patients.destroy', $p);
        $csrf      = csrf_token();
        $action    = '
            <div class="d-flex flex-wrap gap-1">
                <a class="btn btn-sm btn-info" title="View" href="' . $showUrl . '"><i class="fa-solid fa-eye"></i></a>
                <a class="btn btn-sm btn-warning" title="Edit" href="' . $editUrl . '"><i class="fa-solid fa-pen-to-square"></i></a>
                <a class="btn btn-sm btn-success" title="Health Card" href="' . $hcUrl . '" target="_blank"><i class="fa-solid fa-id-card"></i></a>
                <form method="POST" action="' . $deleteUrl . '" onsubmit="return confirm(\'Delete this patient?\')" class="m-0">
                    <input type="hidden" name="_token" value="' . $csrf . '">
                    <input type="hidden" name="_method" value="DELETE">
                    <button class="btn btn-sm btn-danger" type="submit"><i class="fa-solid fa-trash"></i></button>
                </form>
            </div>';

        return [
            'id'           => $p->id,
            'patient'      => $patient,
            'contact'      => $contact,
            'demographics' => $demo,
            'status'       => $status,
            'action'       => $action,
        ];
    }

    public function create()
    {
        return view('patients.create');
    }

    public function store(PatientRequest $request)
    {
        $data = $request->validated();

        // checkbox fix (unchecked won't come)
        $data['is_ipd']    = (bool) ($request->input('is_ipd', 0));
        $data['is_dead']   = (bool) ($request->input('is_dead', 0));
        $data['is_active'] = (bool) ($request->input('is_active', 1));

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('patients', 'public');
        }

        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();

        $patient = Patient::create($data);

        return redirect()
            ->route('patients.index', $patient->id)
            ->with('success', 'Patient created successfully.');
    }

    public function show(Patient $patient)
    {
        if (request()->ajax() || request()->wantsJson()) {
            $data        = $patient->toArray();
            $data['age'] = calculateAgeFromDob($patient->dob);
            return response()->json($data);
        }

        $patient->load([
            'opdPatients.doctor',
            'opdPatients.department',
            'ipdPatients.doctor',
            'ipdPatients.department',
            'erPatients.doctor',
            'erPatients.department',
            'appointments.doctorRelation',
            'prescriptions.doctor',
            'pharmacyTransactions',
            'labOrders.doctor',
            'transactions',
            'histories',
        ]);

        return view('patients.show', compact('patient'));
    }

    public function edit(Patient $patient)
    {
        return view('patients.edit', compact('patient'));
    }

    public function update(PatientRequest $request, Patient $patient)
    {
        $data = $request->validated();

        $data['is_ipd']    = (bool) ($request->input('is_ipd', false));
        $data['is_dead']   = (bool) ($request->input('is_dead', false));
        $data['is_active'] = (bool) ($request->input('is_active', true));

        if ($request->hasFile('image')) {
            if ($patient->image && Storage::disk('public')->exists($patient->image)) {
                Storage::disk('public')->delete($patient->image);
            }
            $data['image'] = $request->file('image')->store('patients', 'public');
        }

        $data['updated_by'] = Auth::id();

        $patient->update($data);

        if ($request->input('_redirect') === 'back') {
            return redirect()->back()->with('success', 'Patient updated successfully.');
        }

        return redirect()
            ->route('patients.index', $patient->id)
            ->with('success', 'Patient updated successfully.');
    }

    public function destroy(Patient $patient)
    {
        if ($patient->image && Storage::disk('public')->exists($patient->image)) {
            Storage::disk('public')->delete($patient->image);
        }

        $patient->delete();

        return redirect()
            ->route('patients.index')
            ->with('success', 'Patient deleted successfully.');
    }
}
