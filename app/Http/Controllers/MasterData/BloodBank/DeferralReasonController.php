<?php
namespace App\Http\Controllers\MasterData\BloodBank;

use App\Models\BloodBank\DeferralReason;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DeferralReasonController extends BaseMasterController
{
    protected string $modelClass = DeferralReason::class;
    protected string $routeName = 'bb.deferral-reasons';
    protected string $viewPath  = 'master-data.deferral_reasons';
    protected string $title      = 'Deferral Reason';

    protected ?string $codePrefix = 'DEF';
    protected ?string $codeColumn = 'deferral_code';

    protected array $searchColumns = ['deferral_code', 'deferral_reason', 'deferral_type', 'regulatory_reference'];
    protected string $orderBy      = 'deferral_type';
    protected string $orderDir     = 'asc';

    protected function rulesStore(Request $request): array
    {
        return [
            'deferral_reason'       => ['required', 'string', 'max:200'],
            'deferral_type'         => ['required', Rule::in(['TEMP', 'PERM'])],
            'default_duration_days' => ['nullable', 'integer', 'min:1'],
            'regulatory_reference'  => ['nullable', 'string', 'max:200'],
            'is_active'             => ['nullable', 'boolean'],
        ];
    }

    protected function rulesUpdate(Request $request, Model $model): array
    {
        return $this->rulesStore($request);
    }

    protected function beforeStore(Request $request, array &$data): void
    {
        $this->validateTempPerm($data);
    }

    protected function beforeUpdate(Request $request, Model $model, array &$data): void
    {
        $this->validateTempPerm($data);
    }

    private function validateTempPerm(array &$data): void
    {
        if (($data['deferral_type'] ?? null) === 'TEMP' && empty($data['default_duration_days'])) {
            throw ValidationException::withMessages([
                'default_duration_days' => 'TEMP deferral must have default duration days.',
            ]);
        }

        if (($data['deferral_type'] ?? null) === 'PERM') {
            $data['default_duration_days'] = null;
        }
    }
}
