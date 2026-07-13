<?php
namespace App\Http\Controllers\MasterData\BloodBank;

use App\Models\BloodBank\StorageLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StorageLocationController extends BaseMasterController
{
    protected string $modelClass = StorageLocation::class;
    protected string $routeName = 'bb.storage-locations';
    protected string $viewPath  = 'master-data.storage_locations';
    protected string $title      = 'Storage Location';

    protected ?string $codePrefix = 'LOC';
    protected ?string $codeColumn = 'location_code';

    protected array $searchColumns = ['location_code', 'location_name', 'location_type', 'status', 'device_id'];
    protected string $orderBy      = 'location_name';
    protected string $orderDir     = 'asc';

    protected function rulesStore(Request $request): array
    {
        return [
            'location_name'                   => ['required', 'string', 'max:150'],
            'location_type'                   => ['required', Rule::in(['BLOOD_BANK', 'REFRIGERATOR', 'DEEP_FREEZER'])],
            'capacity_units'                  => ['required', 'integer', 'min:0'],
            'temperature_monitoring_required' => ['required', 'boolean'],
            'device_id'                       => ['nullable', 'string', 'max:100'],
            'status'                          => ['required', Rule::in(['ACTIVE', 'MAINTENANCE'])],
        ];
    }

    protected function rulesUpdate(Request $request, Model $model): array
    {
        return $this->rulesStore($request);
    }

    // Storage location doesn’t have is_active; override soft delete to set MAINTENANCE
    protected function softDelete(Request $request, Model $model): void
    {
        $payload = ['status' => 'MAINTENANCE'];
        if ($this->hasColumn('updated_by')) {
            $payload['updated_by'] = auth()->id();
        }

        $model->update($payload);
    }
}
