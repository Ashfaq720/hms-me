@include('unite-type._form', [
    'action' => route('admin.unite-types.update', $uniteType->id),
    'method' => 'PUT',
    'buttonText' => 'Update',
    'uniteType' => $uniteType,
])
