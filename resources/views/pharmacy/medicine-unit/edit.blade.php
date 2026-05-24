@include('pharmacy.medicine-unit._form', [
    'action' => route('admin.medicine-units.update', $uniteType->id),
    'method' => 'PUT',
    'buttonText' => 'Update',
    'uniteType' => $uniteType,
])
