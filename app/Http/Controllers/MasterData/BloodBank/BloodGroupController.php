<?php
namespace App\Http\Controllers\MasterData\BloodBank;

use App\Models\BloodBank\BloodGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BloodGroupController extends BaseMasterController
{
    protected string $modelClass  = BloodGroup::class;
    protected ?string $codePrefix = 'BG';

    protected string $routeName = 'bb.blood-groups';
    protected string $viewPath  = 'master-data.blood_groups';

    protected array $searchColumns   = ['code', 'abo_group', 'rh_factor', 'display_name'];
    protected string $defaultSort    = 'abo_group';
    protected string $defaultSortDir = 'asc';

    protected function rulesStore(Request $request): array
    {
        return [
            'abo_group'          => ['required', Rule::in(['A', 'B', 'AB', 'O'])],
            'rh_factor'           => ['required', Rule::in(['POS', 'NEG'])],
            'display_name' => ['required', 'string', 'max:100'],
            'is_active'    => ['nullable', 'boolean'],
        ];
    }

    protected function rulesUpdate(Request $request, Model $model): array
    {
        // NON-NEGOTIABLE: do not allow changing ABO/RH once created
        return [
            'display_name' => ['required', 'string', 'max:100'],
            'is_active'    => ['nullable', 'boolean'],
        ];
    }

    // NON-NEGOTIABLE: blood groups cannot be deleted at all
    public function destroy($id)
    {
        return redirect()
            ->route($this->routeName . '.index')
            ->with('error', 'Blood groups cannot be deleted. You may set inactive only.');
    }
}
