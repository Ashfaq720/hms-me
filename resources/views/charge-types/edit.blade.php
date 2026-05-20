@include('charge-types._form', [
    'action' => route('admin.charge-types.update', $chargeType->id),
    'method' => 'PUT',
    'buttonText' => 'Update',
    'chargeType' => $chargeType,
])
