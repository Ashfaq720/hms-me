@include('pharmacy.medicine-unit._form', [
    'action' => route('admin.medicine-units.store'),
    'method' => 'POST',
    'buttonText' => 'Save',
    'medicalGroup' => null,
])
