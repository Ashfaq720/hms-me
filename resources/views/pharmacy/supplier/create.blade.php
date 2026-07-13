@include('pharmacy.supplier._form', [
    'action' => route('admin.suppliers.store'),
    'method' => 'POST',
    'buttonText' => 'Save',
    'supplier' => null,
])
