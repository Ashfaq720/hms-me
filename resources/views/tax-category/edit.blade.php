@include('tax-category._form', [
    'action' => route('admin.tax-categories.update', $taxCategory->id),
    'method' => 'PUT',
    'buttonText' => 'Update',
    'uniteType' => $taxCategory,
])
