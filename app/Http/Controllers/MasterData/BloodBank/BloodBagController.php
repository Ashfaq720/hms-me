<?php
namespace App\Http\Controllers\MasterData\BloodBank;

use App\Models\BloodBank\BloodBag;
use App\Models\BloodBank\Component;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BloodBagController extends BaseMasterController
{
    protected string $modelClass   = BloodBag::class;
    protected string $routeName    = 'bb.blood-bags';
    protected string $viewPath     = 'master-data.blood_bags';
    protected array $with          = ['components'];
    protected array $searchColumns = ['code', 'bag_type'];

    protected function rulesStore(Request $request): array
    {
        return [
            'bag_type'        => ['required', Rule::in(['SINGLE', 'DOUBLE', 'TRIPLE'])],
            'volume_ml'       => ['required', 'integer', 'min:50'],
            'component_ids'   => ['required', 'array', 'min:1'],
            'component_ids.*' => ['required', 'exists:components,id'],
            'is_active'       => ['nullable', 'boolean'],
        ];
    }

    protected function rulesUpdate(Request $request, Model $model): array
    {
        return [
            'bag_type'        => ['required', Rule::in(['SINGLE', 'DOUBLE', 'TRIPLE'])],
            'volume_ml'       => ['required', 'integer', 'min:50'],
            'component_ids'   => ['required', 'array', 'min:1'],
            'component_ids.*' => ['required', 'exists:components,id'],
            'is_active'       => ['nullable', 'boolean'],
        ];
    }

    protected function afterStore(Request $request, Model $model, array $validated): void
    {
        $model->components()->sync($validated['component_ids']);
    }

    protected function afterUpdate(Request $request, Model $model, array $validated): void
    {
        $model->components()->sync($validated['component_ids']);
    }

    // Optional: pass components list to create/edit pages
    public function create()
    {
        return view($this->viewPath . '.create', [
            'title'      => $this->title,
            'routeName'  => $this->routeName,
            'components' => Component::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function edit($id)
    {
        $item = $this->findOrFail($id);

        return view($this->viewPath . '.edit', [
            'title'      => $this->title,
            'routeName'  => $this->routeName,
            'item'       => $item,
            'components' => Component::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    protected function extraFormData(Request $request, ?Model $model = null): array
    {
        return [
            'components' => Component::where('is_active', true)->orderBy('component_name')->get(),
        ];
    }

}
