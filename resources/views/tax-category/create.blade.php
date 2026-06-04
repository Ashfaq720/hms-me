@include('tax-category._form', [
    'action' => route('admin.tax-categories.store'),
    'method' => 'POST',
    'buttonText' => 'Save',
    'uniteType' => null,
])
