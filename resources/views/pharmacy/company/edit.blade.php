@include('pharmacy.company._form', [
    'action' => route('admin.companies.update', $company->id),
    'method' => 'PUT',
    'buttonText' => 'Update',
    'company' => $company,
])
