@include('unite-type._form', [
    'action' => route('admin.unite-types.store'),
    'method' => 'POST',
    'buttonText' => 'Save',
    'uniteType' => null,
])
