@include('pharmacy.company._form', [
    'action' => route('admin.companies.store'),
    'method' => 'POST',
    'buttonText' => 'Save',
    'company' => null,
])
