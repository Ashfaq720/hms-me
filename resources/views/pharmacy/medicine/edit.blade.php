@include('pharmacy.medicine._form', [
    'action' => route('admin.medicines.update', $medicine->id),
    'method' => 'PUT',
    'buttonText' => 'Update',
    'medicine' => $medicine,
    'medicine_categories' => $medicine_categories,
    'companies' => $companies,
    'medical_groups' => $medical_groups,
    'medicine_units' => $medicine_units,
])
