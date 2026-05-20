@include('pharmacy.medicine._form', [
    'action' => route('admin.medicines.store'),
    'method' => 'POST',
    'buttonText' => 'Save',
    'medicine' => null,
    'medicine_categories' => $medicine_categories,
    'companies' => $companies,
    'medical_groups' => $medical_groups,
    'medicine_units' => $medicine_units,
])
