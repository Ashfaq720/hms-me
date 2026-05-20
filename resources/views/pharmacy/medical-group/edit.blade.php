@include('pharmacy.medical-group._form', [
    'action' => route('admin.medical-groups.update', $medicalGroup->id),
    'method' => 'PUT',
    'buttonText' => 'Update',
    'medicalGroup' => $medicalGroup,
])
