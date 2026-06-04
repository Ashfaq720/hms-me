@include('pharmacy.medical-group._form', [
    'action' => route('admin.medical-groups.store'),
    'method' => 'POST',
    'buttonText' => 'Save',
    'medicalGroup' => null,
])
