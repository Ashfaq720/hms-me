<?php
namespace App\Http\Controllers\MasterData\BloodBank;

use App\Http\Controllers\MasterData\BloodBank\BaseMasterController;
use App\Models\BloodBank\Component;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ComponentController extends BaseMasterController
{
    protected string $modelClass   = Component::class;
    protected ?string $codePrefix  = 'CMP';
    protected string $routeName    = 'bb.components';
    protected string $viewPath     = 'master-data.components';
    protected string $title        = 'Component';
    protected array $with          = ['temperatureRule'];
    protected array $searchColumns = ['code', 'component_name', 'storage_requirement'];

    protected function rulesStore(Request $request): array
    {
        return [
            'component_name'      => ['required', 'string', 'max:120', 'unique:components,component_name'],
            'derived_from'        => ['required', Rule::in(['WHOLE_BLOOD', 'COMPONENT'])],
            'shelf_life_value'    => ['required', 'integer', 'min:1'],
            'shelf_life_unit'     => ['required', Rule::in(['HOURS', 'DAYS'])],
            'storage_requirement' => ['required', Rule::in(['BLOOD_BANK', 'REFRIGERATOR', 'DEEP_FREEZER'])],
            'min_volume_ml'       => ['nullable', 'integer', 'min:1'],
            'max_volume_ml'       => ['nullable', 'integer', 'gte:min_volume_ml'],
            'is_active'           => ['boolean'],
        ];
    }

    protected function rulesUpdate(Request $request, Model $model): array
    {
        return [
            'component_name'      => ['sometimes', 'required', 'string', 'max:120', Rule::unique('components', 'component_name')->ignore($model->id)],
            'derived_from'        => ['sometimes', 'required', Rule::in(['WHOLE_BLOOD', 'COMPONENT'])],
            'shelf_life_value'    => ['sometimes', 'required', 'integer', 'min:1'],
            'shelf_life_unit'     => ['sometimes', 'required', Rule::in(['HOURS', 'DAYS'])],
            'storage_requirement' => ['sometimes', 'required', Rule::in(['BLOOD_BANK', 'REFRIGERATOR', 'DEEP_FREEZER'])],
            'min_volume_ml'       => ['sometimes', 'nullable', 'integer', 'min:1'],
            'max_volume_ml'       => ['sometimes', 'nullable', 'integer', 'gte:min_volume_ml'],
            'is_active'           => ['sometimes', 'boolean'],
        ];
    }
}
