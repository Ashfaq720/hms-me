@include('pharmacy.medicine-category._form', [
    'action' => route('admin.medicine-categories.update', $medicineCategory->id),
    'method' => 'PUT',
    'buttonText' => 'Update',
    'medicineCategory' => $medicineCategory,
])
