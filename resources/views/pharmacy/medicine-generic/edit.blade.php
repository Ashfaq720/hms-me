@include('pharmacy.medicine-generic._form', [
    'action' => route('admin.medicine-generics.update', $medicineGeneric->id),
    'method' => 'PUT',
    'buttonText' => 'Update',
    'medicineGeneric' => $medicineGeneric,
])
