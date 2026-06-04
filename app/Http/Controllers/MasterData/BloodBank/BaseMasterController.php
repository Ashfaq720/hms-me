<?php
namespace App\Http\Controllers\MasterData\BloodBank;

use App\Http\Controllers\Controller;
use App\Models\BloodBank\MasterAuditLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

abstract class BaseMasterController extends Controller
{
    protected string $modelClass;
    protected string $routeName = '';
    protected string $viewPath  = '';
    protected string $title     = '';

    protected array $with            = [];
    protected array $searchColumns   = [];
    protected string $defaultSort    = 'id';
    protected string $defaultSortDir = 'desc';
    protected int $perPage           = 15;

    protected function extraFormData(Request $request, ?Model $model = null): array
    {
        return []; // default: nothing extra
    }

    public function __construct()
    {
        $this->middleware(['auth']);
        // optional:
        // $this->middleware('role:ADMIN')->except(['index']);
    }

    abstract protected function rulesStore(Request $request): array;
    abstract protected function rulesUpdate(Request $request, Model $model): array;

    protected function baseQuery(Request $request): Builder
    {
        /** @var Builder $q */
        $q = ($this->modelClass)::query();

        if (! empty($this->with)) {
            $q->with($this->with);
        }

        $term = trim((string) $request->query('q', ''));
        if ($term !== '' && ! empty($this->searchColumns)) {
            $q->where(function (Builder $sub) use ($term) {
                foreach ($this->searchColumns as $col) {
                    $sub->orWhere($col, 'like', "%{$term}%");
                }
            });
        }

        if ($request->has('active') && $this->hasColumn('is_active')) {
            $q->where('is_active', (bool) $request->query('active'));
        }

        $sort = (string) $request->query('sort', $this->defaultSort);
        $dir  = strtolower((string) $request->query('dir', $this->defaultSortDir)) === 'asc' ? 'asc' : 'desc';
        $q->orderBy($sort, $dir);

        return $q;
    }

    public function index(Request $request)
    {
        $items = $this->baseQuery($request)
            ->paginate((int) $request->query('per_page', $this->perPage))
            ->withQueryString();

        return view($this->viewPath . '.index', array_merge([
            'title'     => $this->title,
            'items'     => $items,
            'routeName' => $this->routeName,
            'q'         => $request->query('q', ''),
        ], $this->extraFormData($request)));
    }

    public function create()
    {
        return view($this->viewPath . '.create', [
            'title'     => $this->title,
            'routeName' => $this->routeName,
        ]);
    }

    public function store(Request $request)
    {
        // dd($request->all());
        try {
            // ✅ Validation (will throw ValidationException automatically)
            $data = $request->validate($this->rulesStore($request));

            // ✅ Audit
            if ($this->hasColumn('created_by')) {
                $data['created_by'] = auth()->id();
            }

            /** @var \Illuminate\Database\Eloquent\Model $model */
            $model = ($this->modelClass)::create($data);

            $this->afterStore($request, $model, $data);

            return redirect()
                ->route($this->routeName . '.index')
                ->with('success', $this->title . ' created successfully.');
        } catch (\Throwable $e) {
            // dd($e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Something went wrong. Please try again.');
        }
    }

    public function edit($id)
    {
        $item = $this->findOrFail($id);

        return view($this->viewPath . '.edit', [
            'title'     => $this->title,
            'item'      => $item,
            'routeName' => $this->routeName,
        ]);
    }

    public function update(Request $request, $id)
    {
        $item = $this->findOrFail($id);

        if ($this->hasColumn('is_locked') && (bool) $item->getAttribute('is_locked') === true) {
            throw ValidationException::withMessages([
                'message' => 'This record is locked/used and cannot be edited.',
            ]);
        }

        $data = $request->validate($this->rulesUpdate($request, $item));

        if ($this->hasColumn('updated_by')) {
            $data['updated_by'] = auth()->id();
        }

        $this->beforeUpdate($request, $item, $data);
        $item->update($data);
        $this->afterUpdate($request, $item, $data);

        return redirect()
            ->route($this->routeName . '.index')
            ->with('success', $this->title . ' updated successfully.');
    }

    public function destroy($id)
    {
        $item = $this->findOrFail($id);

        if ($this->hasColumn('is_active')) {
            $update = ['is_active' => false];
            if ($this->hasColumn('updated_by')) {
                $update['updated_by'] = auth()->id();
            }

            $item->update($update);

            return redirect()
                ->route($this->routeName . '.index')
                ->with('success', $this->title . ' set to inactive.');
        }

        return redirect()
            ->route($this->routeName . '.index')
            ->with('error', 'Hard delete is not allowed.');
    }

    // Optional: lock endpoint for “first usage locks the master”
    public function lock($id)
    {
        $item = $this->findOrFail($id);

        if (! $this->hasColumn('is_locked')) {
            return back()->with('error', 'Locking not supported for this master.');
        }

        $update = ['is_locked' => true];
        if ($this->hasColumn('updated_by')) {
            $update['updated_by'] = auth()->id();
        }

        $item->update($update);

        return back()->with('success', $this->title . ' locked successfully.');
    }

    // Hooks
    protected function afterStore(Request $request, Model $model, array $validated): void
    {}
    protected function beforeUpdate(Request $request, Model $model, array &$validated): void
    {}
    protected function afterUpdate(Request $request, Model $model, array $validated): void
    {}

    protected function findOrFail($id): Model
    {
        $q = ($this->modelClass)::query();
        if (! empty($this->with)) {
            $q->with($this->with);
        }

        return $q->findOrFail($id);
    }

    protected function hasColumn(string $column): bool
    {
        try {
            return \Schema::hasColumn((new $this->modelClass)->getTable(), $column);
        } catch (\Throwable $e) {
            return false;
        }
    }

    // protected function afterStore(Request $request, Model $model, array $data): void
    // {
    //     MasterAuditLog::create([ // or module_key
    //         'action'       => 'CREATE',
    //         'master_table'   => $model->getTable(),
    //         'record_id'    => $model->getKey(),
    //         'old_value'   => null,
    //         'new_value'   => $model->getAttributes(), // or only changed fields
    //         'action_by' => auth()->id(),
    //         'action_at' => now(),
    //         'ip'           => $request->ip(),
    //     ]);
    // }
}
