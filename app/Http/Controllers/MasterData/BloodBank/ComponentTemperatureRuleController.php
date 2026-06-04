<?php
namespace App\Http\Controllers\MasterData\BloodBank;

use App\Models\BloodBank\Component;
use App\Models\BloodBank\ComponentTemperatureRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ComponentTemperatureRuleController extends BaseMasterController
{
    protected string $modelClass = ComponentTemperatureRule::class;
    protected string $routeName = 'bb.temperature-rules';
    protected string $viewPath  = 'master-data.temperature_rules';
    protected string $title      = 'Temperature Rule';
    protected array $with      = ['component'];
    protected string $orderBy  = 'id';
    protected string $orderDir = 'desc';

    protected function rulesStore(Request $request): array
    {
        return [
            'component_id'        => ['required', 'exists:components,id', 'unique:component_temperature_rules,component_id'],
            'min_temp'          => ['required', 'numeric'],
            'max_temp'          => ['required', 'numeric', 'gte:min_temp'],
            'monitoring_required' => ['required', 'boolean'],
            'is_active'           => ['nullable', 'boolean'],
        ];
    }

    protected function rulesUpdate(Request $request, Model $model): array
    {
        // component_id not editable
        return [
            'min_temp'          => ['required', 'numeric'],
            'max_temp'          => ['required', 'numeric', 'gte:min_temp'],
            'monitoring_required' => ['required', 'boolean'],
            'is_active'           => ['nullable', 'boolean'],
        ];
    }

    protected function extraFormData(Request $request, ?Model $model = null): array
    {
        return [
            'components' => Component::where('is_active', true)->orderBy('component_name')->get(),
        ];
    }
}
