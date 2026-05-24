@include('pharmacy.medicine-category._form', [
    'action' => route('admin.medicine-categories.store'),
    'method' => 'POST',
    'buttonText' => 'Save',
    'medicineCategory' => null,
])
