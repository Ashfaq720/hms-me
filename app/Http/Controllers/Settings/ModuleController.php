<?php

namespace App\Http\Controllers\Settings;

use Exception;
use App\Models\Module;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Application\Module\DTO\CreateModuleDTO;
use App\Application\Module\DTO\UpdateModuleDTO;
use App\Application\Module\Actions\GetModuleAction;
use App\Application\Module\Actions\CreateModuleAction;
use App\Application\Module\Actions\DeleteModuleAction;
use App\Application\Module\Actions\UpdateModuleAction;

class ModuleController extends Controller
{
    protected CreateModuleAction $createModuleAction;
    protected UpdateModuleAction $updateModuleAction;
    protected DeleteModuleAction $deleteModuleAction;
    protected GetModuleAction $getModuleAction;

    public function __construct(
        CreateModuleAction $createModuleAction,
        UpdateModuleAction $updateModuleAction,
        DeleteModuleAction $deleteModuleAction,
        GetModuleAction $getModuleAction
    ) {
        $this->createModuleAction = $createModuleAction;
        $this->updateModuleAction = $updateModuleAction;
        $this->deleteModuleAction = $deleteModuleAction;
        $this->getModuleAction = $getModuleAction;
    }

    /**
     * Display a listing of the modules.
     */
    public function index(Request $request): View
    {
        $perPage = $request->get('per_page', 15);
        $filters = $request->only(['search', 'active']);

        $modules = $this->getModuleAction->getPaginated($perPage, $filters);

        return view('backend.modules.index', compact('modules'));
    }

    /**
     * Show the form for creating a new module.
     */
    public function create(): View
    {
        return view('backend.modules.create');
    }

    /**
     * Store a newly created module in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|regex:/^#[a-fA-F0-9]{6}$/',
            'is_active' => 'boolean',
            'modules' => 'nullable|array',
            'modules.*.name' => 'required|string|max:255',
            'modules.*.id' => 'nullable|integer|exists:permissions,id'
        ]);

        try {
            DB::beginTransaction();

            $dto = CreateModuleDTO::fromRequest($validated);
            $module = $this->createModuleAction->execute($dto);

            DB::commit();

            return redirect()->route('modules.show', $module->id)
                           ->with('success', 'Module created successfully.');

        } catch (Exception $e) {
            DB::rollBack();

            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Failed to create module: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified module.
     */
    public function show(int $id): View
    {
        $module = $this->getModuleAction->execute($id);

        return view('backend.modules.show', compact('module'));
    }

    /**
     * Show the form for editing the specified module.
     */
    public function edit(int $id): View
    {
        $module = $this->getModuleAction->execute($id);

        // For editing, we need the full Eloquent model with relationships
        $eloquentModule = Module::with('permissions')->findOrFail($id);

        return view('backend.modules.edit', compact('module', 'eloquentModule'));
    }

    /**
     * Update the specified module in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|regex:/^#[a-fA-F0-9]{6}$/',
            'is_active' => 'boolean',
            'modules' => 'nullable|array',
            'modules.*.name' => 'required|string|max:255',
            'modules.*.id' => 'nullable|integer|exists:permissions,id'
        ]);

        try {
            DB::beginTransaction();

            $module = $this->getModuleAction->execute($id);
            $dto = UpdateModuleDTO::fromRequest($validated);
            $updatedModule = $this->updateModuleAction->execute($module, $dto);

            DB::commit();

            return redirect()->route('modules.show', $updatedModule->id)
                           ->with('success', 'Module updated successfully.');

        } catch (Exception $e) {
            DB::rollBack();

            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Failed to update module: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified module from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $module = $this->getModuleAction->execute($id);
            $this->deleteModuleAction->execute($module);

            return redirect()->route('modules.index')
                           ->with('success', 'Module deleted successfully.');

        } catch (Exception $e) {
            return redirect()->back()
                           ->with('error', 'Failed to delete module: ' . $e->getMessage());
        }
    }
}
