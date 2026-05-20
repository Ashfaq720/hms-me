@include('charge-types._form', [
    'action' => route('admin.charge-types.store'),
    'method' => 'POST',
    'buttonText' => 'Save',
    'chargeType' => null,
])
