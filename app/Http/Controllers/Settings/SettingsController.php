<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Application\Settings\Actions\GetSettingsByGroupAction;
use App\Application\Settings\Actions\UpdateSettingAction;
use App\Application\Settings\Actions\UpdateMultipleSettingsAction;
use App\Application\Settings\DTO\UpdateSettingDTO;
use App\Application\Settings\DTO\UpdateMultipleSettingsDTO;
use App\Domain\Settings\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    public function __construct(
        private readonly SettingService $settingService,
        private readonly GetSettingsByGroupAction $getSettingsByGroupAction,
        private readonly UpdateSettingAction $updateSettingAction,
        private readonly UpdateMultipleSettingsAction $updateMultipleSettingsAction
    ) {
        $this->middleware(['auth', 'can:setting_access']);
    }

    // Index: overview per group
    public function index(): View
    {
        try {
            $groups = $this->settingService->getAllGroups();
            $allSettings = collect();

            foreach ($groups as $group) {
                $settings = $this->getSettingsByGroupAction->execute($group);
                $allSettings->put($group, $settings);
            }

            return view('backend.settings.index', compact('allSettings', 'groups'));
        } catch (\Exception $e) {
            return view('backend.settings.home')
                ->withErrors(['error' => 'Unable to load settings. Please try again.']);
        }
    }

    // All settings page with tabbed groups
    public function all(): View|RedirectResponse
    {
        try {
            $groups = $this->settingService->getAllGroups();
            $allSettings = collect();

            foreach ($groups as $group) {
                $settings = $this->getSettingsByGroupAction->execute($group);
                $allSettings->put($group, $settings);
            }

            return view('backend.settings.all', compact('allSettings', 'groups'));
        } catch (\Exception $e) {
            return redirect()->route('settings.index')
                ->withErrors(['error' => 'Unable to load settings. Please try again.']);
        }
    }

    // Show group settings collection
    public function group(string $group): View|RedirectResponse
    {
        try {
            $settings = $this->getSettingsByGroupAction->execute($group);
            $allGroups = $this->settingService->getAllGroups();

            return view('backend.settings.group', compact('settings', 'group', 'allGroups'));
        } catch (\Exception $e) {
            return redirect()->route('settings.index')
                ->with('toast_error', "Unable to load settings for group '{$group}'. Please try again.");
        }
    }

    // Show bulk edit form for a group
    public function groupEdit(string $group): View|RedirectResponse
    {
        try {
            $settings = $this->getSettingsByGroupAction->execute($group);

            return view('backend.settings.group-edit', compact('settings', 'group'));
        } catch (\Exception $e) {
            return redirect()->route('settings.index')
                ->with('toast_error', "Unable to load settings for group '{$group}'. Please try again.");
        }
    }

    // Show the create form for a specific group
    public function create(string $group): View|RedirectResponse
    {
        try {
            // return the create view with default group
            return view('backend.settings.create', compact('group'));
        } catch (\Exception $e) {
            return redirect()->route('settings.index')
                ->with('toast_error', "Unable to load create form for group '{$group}'. Please try again.");
        }
    }

    // Show a single setting
    public function show(Setting $setting): View|RedirectResponse
    {
        try {
            return view('backend.settings.show', compact('setting'));
        } catch (\Exception $e) {
            return redirect()->route('settings.index')
                ->with('toast_error', "Unable to load setting. Please try again.");
        }
    }

    // Store a newly created setting
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'key' => ['required', 'string', 'max:255', Rule::unique('settings', 'key')->where(function ($query) use ($request) {
                if ($request->filled('group')) {
                    $query->where('group', $request->input('group'));
                }
            })],
            'value' => 'required',
            'type' => 'required|string|in:string,integer,float,boolean,json,array,file,image',
            'group' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'is_public' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            $data = $request->only(['key', 'value', 'type', 'group', 'description', 'is_public', 'is_active']);
            $setting = Setting::create($data);

            DB::commit();
            return redirect()->route('settings.show', $setting)
                ->with('toast_success', "Setting '{$setting->key}' created successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('toast_error', $e->getMessage());
        }
    }

    // Show edit form for a setting
    public function edit(Setting $setting): View|RedirectResponse
    {
        try {
            return view('backend.settings.edit', compact('setting'));
        } catch (\Exception $e) {
            return redirect()->route('settings.index')->with('toast_error', $e->getMessage());
        }
    }

    public function update(Request $request, Setting $setting): RedirectResponse
    {
        $validated = $request->validate([
            'key' => 'required|string|max:255',
            'value' => 'required',
            'type' => 'required|string|in:string,integer,float,boolean,json,array',
            'group' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'is_public' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            // Ensure key is unique within group (ignore current)
            if ($request->filled('key') && $request->filled('group')) {
                $exists = Setting::where('group', $request->input('group'))
                    ->where('key', $request->input('key'))
                    ->where('id', '!=', $setting->id)
                    ->exists();
                if ($exists) {
                    return redirect()->back()->withInput()->with('toast_error', 'Setting key already exists in this group.');
                }
            }

            $dto = UpdateSettingDTO::fromRequest($validated);
            $setting = $this->updateSettingAction->execute($dto);

            DB::commit();

            return redirect()->back()
                ->with('toast_success', "Setting '{$setting->key}' updated successfully.");
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('toast_error', $e->getMessage());
        }
    }

    public function updateMultiple(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'required|array',
            'settings.*.value' => 'required',
            'settings.*.type' => 'sometimes|string|in:string,text,integer,float,boolean,json,array',
            'group' => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();

        try {
            $dto = UpdateMultipleSettingsDTO::fromRequest($validated);
            $updatedSettings = $this->updateMultipleSettingsAction->execute($dto);

            DB::commit();

            return redirect()->back()
                ->with('toast_success', "Successfully updated {$updatedSettings->count()} settings.");
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('toast_error', $e->getMessage());
        }
    }

    public function toggle(Request $request, string $key): RedirectResponse
    {
        $request->validate([
            'status' => 'required|boolean',
        ]);

        DB::beginTransaction();

        try {
            $status = $request->boolean('status');
            
            if ($status) {
                $setting = $this->settingService->activate($key);
            } else {
                $setting = $this->settingService->deactivate($key);
            }

            DB::commit();

            return redirect()->back()
                ->with('toast_success', "Setting '{$key}' " . ($status ? 'activated' : 'deactivated') . " successfully.");
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('toast_error', $e->getMessage());
        }
    }

    public function destroy(string $key): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $deleted = $this->settingService->deleteByKey($key);

            if (!$deleted) {
                throw new \Exception("Setting '{$key}' not found or could not be deleted.");
            }

            DB::commit();

            return redirect()->back()
                ->with('toast_success', "Setting '{$key}' deleted successfully.");
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('toast_error', $e->getMessage());
        }
    }
}