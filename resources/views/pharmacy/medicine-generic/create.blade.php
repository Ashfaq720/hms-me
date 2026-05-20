@include('pharmacy.medicine-generic._form', [
    'action' => route('admin.medicine-generics.store'),
    'method' => 'POST',
    'buttonText' => 'Save',
    'medicineGeneric' => null,
])
