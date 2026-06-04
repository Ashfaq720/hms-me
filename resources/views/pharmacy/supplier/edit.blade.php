@include('pharmacy.supplier._form', [
    'action' => route('admin.suppliers.update', $supplier->id),
    'method' => 'PUT',
    'buttonText' => 'Update',
    'supplier' => $supplier,
])
