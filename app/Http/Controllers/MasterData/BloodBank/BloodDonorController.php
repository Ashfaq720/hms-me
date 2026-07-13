<?php
namespace App\Http\Controllers\MasterData\BloodBank;

use App\Models\BloodBank\BloodDonor;
use App\Models\BloodBank\BloodGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class BloodDonorController extends BaseMasterController
{
    protected string $modelClass = BloodDonor::class;
    protected string $routeName  = 'bb.blood-donors';
    protected string $viewPath   = 'master-data.blood_donors';
    protected string $title      = 'Blood Donor';

    protected array $with          = ['bloodGroup'];
    protected array $searchColumns = ['donor_code', 'name', 'father_name', 'contact_no'];

    protected function extraFormData(Request $request, ?Model $model = null): array
    {
        return [
            'bloodGroups' => BloodGroup::where('is_active', true)->orderBy('display_name')->get(),
        ];
    }

    protected function rulesStore(Request $request): array
    {
        return [
            'name'           => ['required', 'string', 'max:150'],
            'dob'            => ['required', 'date', 'before:today'],
            'blood_group_id' => ['required', 'exists:blood_groups,id'],
            'gender'         => ['required', 'in:MALE,FEMALE,OTHER'],
            'father_name'    => ['nullable', 'string', 'max:150'],
            'contact_no'     => ['required', 'string', 'max:20'],
            'address'        => ['nullable', 'string', 'max:500'],
            'is_active'      => ['nullable', 'boolean'],
        ];
    }

    protected function rulesUpdate(Request $request, Model $model): array
    {
        return $this->rulesStore($request);
    }
}
