@include('charge-categories._form', [
    'action' => route('admin.charge-categories.store'),
    'method' => 'POST',
    'buttonText' => 'Save',
    'chargeCategory' => null,
])
