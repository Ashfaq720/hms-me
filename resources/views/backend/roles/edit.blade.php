@extends('backend.layouts.master')

@section('title', 'Edit Role')

@section('content')
    <div class="role-create-container">
        <div class="container pb-5 mb-5">
            <div class="app-page-head animate-slide-in">
                <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between">
                    <div class="clearfix">
                        <h1 class="app-page-title">
                            <i class="fi fi-rr-edit me-2"></i>Edit Role: {{ $roleEntity->name }}
                        </h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('dashboard') }}"><i class="fi fi-rr-home me-1"></i>Dashboard</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('roles.index') }}"><i class="fi fi-rr-shield me-1"></i>Roles</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    <i class="fi fi-rr-edit me-1"></i>Edit {{ $roleEntity->name }}
                                </li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('roles.show', $roleEntity->id) }}" class="btn btn-light btn-outline-enhanced">
                            <i class="fi fi-rr-eye me-1"></i> View Role
                        </a>
                        <a href="{{ route('roles.index') }}" class="btn btn-light btn-outline-enhanced">
                            <i class="fi fi-rr-arrow-left me-1"></i> Back to Roles
                        </a>
                    </div>
                </div>
            </div>

            <form action="{{ route('roles.update', $roleEntity->id) }}" method="POST" class="animate-slide-in">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-8">
                        <div class="card role-card">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fi fi-rr-info me-2"></i>Role Information
                                </h5>
                            </div>
                            <div class="card-body">
                                <!-- Name Field -->
                                <div class="mb-3">
                                    <label for="name" class="form-label">Role Name<span class="text-danger"> *</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        id="name" name="name" value="{{ old('name', $roleEntity->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Description Field -->
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                        rows="3">{{ old('description', $roleEntity->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Active Status -->
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input form-check-input-custom" type="checkbox"
                                            id="is_active" name="is_active" value="1"
                                            {{ old('is_active', $roleEntity->isActive ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label form-label-enhanced" for="is_active">
                                            <i class="fi fi-rr-toggle-on me-1"></i>Active Role
                                        </label>
                                        <div class="form-text text-muted">
                                            <i class="fi fi-rr-info me-1"></i>Only active roles can be assigned to users
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <!-- Role Guidelines -->
                        <div class="card role-card">
                            <div class="card-header">
                                <h6 class="card-title">
                                    <i class="fi fi-rr-lightbulb-on me-2"></i>Role Guidelines
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="small">
                                    <h6 class="text-primary mb-3">
                                        <i class="fi fi-rr-star me-1"></i>Best Practices:
                                    </h6>
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2">
                                            <i class="fi fi-rr-check text-success me-2"></i>
                                            Use descriptive role names
                                        </li>
                                        <li class="mb-2">
                                            <i class="fi fi-rr-check text-success me-2"></i>
                                            Assign minimal required permissions
                                        </li>
                                        <li class="mb-2">
                                            <i class="fi fi-rr-check text-success me-2"></i>
                                            Set appropriate priority levels
                                        </li>
                                        <li class="mb-2">
                                            <i class="fi fi-rr-check text-success me-2"></i>
                                            Review permissions regularly
                                        </li>
                                        <li class="mb-0">
                                            <i class="fi fi-rr-check text-success me-2"></i>
                                            Document role responsibilities
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Permissions Assignment -->
                @php
                    $selectedIds = $eloquentRole->permissions->pluck('id')->toArray();
                    $totalPerms = $modules->sum(fn ($m) => $m->permissions->count());
                @endphp
                <div class="card role-card mt-4 permissions-tabs">
                    <div class="card-header">
                        <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
                            <div class="d-flex flex-wrap align-items-center gap-3">
                                <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                                    <i class="fi fi-rr-shield-check"></i> Permissions
                                </h5>
                                <span class="badge bg-primary px-3 py-2" id="globalSelectedBadge">
                                    <span id="globalSelectedCount">{{ count($selectedIds) }}</span> /
                                    <span id="globalTotalCount">{{ $totalPerms }}</span> selected
                                </span>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <input type="search" id="permSearch" class="form-control form-control-sm" style="min-width:220px;"
                                       placeholder="🔍 Search permissions...">
                                <button type="button" class="btn btn-sm btn-outline-success" id="presetViewOnly" title="Select all view/index/show permissions">
                                    <i class="fi fi-rr-eye"></i> View-Only
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="presetSelectAll">
                                    <i class="fi fi-rr-check-square"></i> Select All
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" id="presetClearAll">
                                    <i class="fi fi-rr-cross-circle"></i> Clear All
                                </button>
                            </div>
                        </div>
                        <ul class="nav nav-underline card-header-tabs" id="myTab" role="tablist">
                            @if (isset($modules) && $modules->count() > 0)
                                @foreach ($modules as $index => $module)
                                    @php
                                        $modSelected = $module->permissions->filter(fn ($p) => in_array($p->id, $selectedIds))->count();
                                        $modTotal = $module->permissions->count();
                                    @endphp
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link {{ $index === 0 ? 'active' : '' }}"
                                            id="module-{{ $module->id }}-tab" data-bs-toggle="tab"
                                            data-bs-target="#module-{{ $module->id }}" type="button" role="tab"
                                            aria-controls="module-{{ $module->id }}"
                                            aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                                            tabindex="{{ $index === 0 ? '0' : '-1' }}"
                                            data-module-id="{{ $module->id }}">
                                            <i class="fi fi-rr-{{ $module->icon ?? 'apps' }} me-1"></i>
                                            {{ $module->name }}
                                            <span class="badge module-counter ms-1" data-module-badge="{{ $module->id }}">
                                                <span class="mod-selected">{{ $modSelected }}</span> /
                                                <span class="mod-total">{{ $modTotal }}</span>
                                            </span>
                                        </button>
                                    </li>
                                @endforeach
                            @endif
                        </ul>
                    </div>

                    <div class="card-body">
                        <div class="tab-content">
                            @foreach ($modules as $index => $module)
                                @php $modSelected = $module->permissions->filter(fn ($p) => in_array($p->id, $selectedIds))->count(); @endphp
                                <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
                                    id="module-{{ $module->id }}" role="tabpanel"
                                    aria-labelledby="module-{{ $module->id }}-tab" tabindex="0"
                                    data-module-pane="{{ $module->id }}">

                                    @if ($module->permissions->count() > 0)
                                        <div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
                                            <span class="text-muted small me-2">Quick actions for <strong>{{ $module->name }}</strong>:</span>
                                            <button type="button" class="btn btn-sm btn-outline-primary module-select-all"
                                                    data-module-target="{{ $module->id }}">
                                                <i class="fi fi-rr-check"></i> Select all in this module
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary module-clear"
                                                    data-module-target="{{ $module->id }}">
                                                <i class="fi fi-rr-cross"></i> Clear all
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-success module-view-only"
                                                    data-module-target="{{ $module->id }}">
                                                <i class="fi fi-rr-eye"></i> View-only
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-info module-invert"
                                                    data-module-target="{{ $module->id }}">
                                                <i class="fi fi-rr-rotate-right"></i> Invert
                                            </button>
                                        </div>
                                        <div class="permissions-grid" data-grid-for="{{ $module->id }}">
                                            @foreach ($module->permissions as $permission)
                                                @php $isSelected = in_array($permission->id, $selectedIds); @endphp
                                                <div class="permission-item {{ $isSelected ? 'selected' : '' }}"
                                                     data-perm-name="{{ strtolower($permission->name) }}"
                                                     data-perm-module="{{ $module->id }}">
                                                    <div class="form-check">
                                                        <input class="form-check-input form-check-input-custom permission-check"
                                                            type="checkbox" id="permission_{{ $permission->id }}"
                                                            name="permissions[]" value="{{ $permission->id }}"
                                                            data-perm-module="{{ $module->id }}"
                                                            {{ $isSelected ? 'checked' : '' }}>
                                                        <label class="form-check-label fw-medium" for="permission_{{ $permission->id }}">
                                                            <div class="permission-name">{{ $permission->name }}</div>
                                                            @if ($permission->description)
                                                                <div class="permission-description">{{ $permission->description }}</div>
                                                            @endif
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="text-center py-4 d-none empty-search-state" data-empty-for="{{ $module->id }}">
                                            <i class="fi fi-rr-search text-muted mb-2" style="font-size: 2rem;"></i>
                                            <p class="text-muted mb-0">No permissions in this module match your search</p>
                                        </div>
                                    @else
                                        <div class="text-center py-4">
                                            <i class="fi fi-rr-info text-muted mb-2" style="font-size: 2rem;"></i>
                                            <p class="text-muted mb-0">No permissions available for this module</p>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-3 justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary-enhanced">
                        <i class="fi fi-rr-check me-2"></i>Update Role
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const VIEW_KEYWORDS = ['view', 'index', 'show', 'list', 'read', 'browse', 'export'];
            const allChecks = () => document.querySelectorAll('.permission-check');
            const checksInModule = mid => document.querySelectorAll(`.permission-check[data-perm-module="${mid}"]`);
            const itemFor = cb => cb.closest('.permission-item');

            function updateItemState(cb) {
                const item = itemFor(cb);
                if (!item) return;
                cb.checked ? item.classList.add('selected') : item.classList.remove('selected');
            }

            function refreshCounters() {
                const all = allChecks();
                const sel = Array.from(all).filter(c => c.checked).length;
                const gs = document.getElementById('globalSelectedCount');
                const gt = document.getElementById('globalTotalCount');
                if (gs) gs.textContent = sel;
                if (gt) gt.textContent = all.length;

                document.querySelectorAll('[data-module-badge]').forEach(badge => {
                    const mid = badge.dataset.moduleBadge;
                    const checks = checksInModule(mid);
                    const checkedCount = Array.from(checks).filter(c => c.checked).length;
                    const selSpan = badge.querySelector('.mod-selected');
                    if (selSpan) selSpan.textContent = checkedCount;
                    badge.classList.remove('bg-secondary', 'bg-success', 'bg-warning', 'text-dark');
                    if (checkedCount === checks.length && checks.length > 0) {
                        badge.classList.add('bg-success');
                    } else if (checkedCount > 0) {
                        badge.classList.add('bg-warning', 'text-dark');
                    } else {
                        badge.classList.add('bg-secondary');
                    }
                });
            }

            // Init state
            allChecks().forEach(cb => {
                updateItemState(cb);
                cb.addEventListener('change', () => { updateItemState(cb); refreshCounters(); });
            });

            // Card click toggles checkbox
            document.querySelectorAll('.permission-item').forEach(item => {
                item.addEventListener('click', function (e) {
                    if (e.target.type === 'checkbox') return;
                    const cb = item.querySelector('.permission-check');
                    if (!cb) return;
                    cb.checked = !cb.checked;
                    cb.dispatchEvent(new Event('change'));
                });
            });

            // ─── Module-level bulk actions ───
            function isViewKeyword(name) {
                const lower = name.toLowerCase();
                return VIEW_KEYWORDS.some(k => lower.endsWith('.' + k) || lower.endsWith(' ' + k) || lower.includes('.' + k + '.'));
            }
            document.querySelectorAll('.module-select-all').forEach(btn => {
                btn.addEventListener('click', () => {
                    checksInModule(btn.dataset.moduleTarget).forEach(cb => { cb.checked = true; updateItemState(cb); });
                    refreshCounters();
                });
            });
            document.querySelectorAll('.module-clear').forEach(btn => {
                btn.addEventListener('click', () => {
                    checksInModule(btn.dataset.moduleTarget).forEach(cb => { cb.checked = false; updateItemState(cb); });
                    refreshCounters();
                });
            });
            document.querySelectorAll('.module-invert').forEach(btn => {
                btn.addEventListener('click', () => {
                    checksInModule(btn.dataset.moduleTarget).forEach(cb => { cb.checked = !cb.checked; updateItemState(cb); });
                    refreshCounters();
                });
            });
            document.querySelectorAll('.module-view-only').forEach(btn => {
                btn.addEventListener('click', () => {
                    checksInModule(btn.dataset.moduleTarget).forEach(cb => {
                        const item = itemFor(cb);
                        const name = item?.dataset.permName || '';
                        cb.checked = isViewKeyword(name);
                        updateItemState(cb);
                    });
                    refreshCounters();
                });
            });

            // ─── Global bulk actions ───
            document.getElementById('presetSelectAll')?.addEventListener('click', () => {
                allChecks().forEach(cb => { cb.checked = true; updateItemState(cb); });
                refreshCounters();
            });
            document.getElementById('presetClearAll')?.addEventListener('click', () => {
                if (!confirm('Clear ALL permissions for this role?')) return;
                allChecks().forEach(cb => { cb.checked = false; updateItemState(cb); });
                refreshCounters();
            });
            document.getElementById('presetViewOnly')?.addEventListener('click', () => {
                allChecks().forEach(cb => {
                    const item = itemFor(cb);
                    const name = item?.dataset.permName || '';
                    cb.checked = isViewKeyword(name);
                    updateItemState(cb);
                });
                refreshCounters();
            });

            // ─── Live search ───
            const search = document.getElementById('permSearch');
            if (search) {
                search.addEventListener('input', () => {
                    const q = search.value.trim().toLowerCase();
                    document.querySelectorAll('[data-grid-for]').forEach(grid => {
                        const moduleId = grid.dataset.gridFor;
                        let visible = 0;
                        grid.querySelectorAll('.permission-item').forEach(item => {
                            const match = !q || item.dataset.permName.includes(q);
                            item.style.display = match ? '' : 'none';
                            if (match) visible++;
                        });
                        const empty = document.querySelector(`[data-empty-for="${moduleId}"]`);
                        if (empty) empty.classList.toggle('d-none', visible !== 0);
                    });
                });
            }

            refreshCounters();

            // Form validation with better UX
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const nameValue = document.getElementById('name').value.trim();

                    if (!nameValue) {
                        e.preventDefault();
                        document.getElementById('name').focus();
                        showNotification('Please enter a role name', 'error');
                        return;
                    }

                    // Show loading state
                    const submitBtn = form.querySelector('button[type=\"submit\"]');
                    if (submitBtn) {
                        submitBtn.innerHTML =
                            '<i class=\"fi fi-rr-spinner animate-spin me-2\"></i>Updating Role...';
                        submitBtn.disabled = true;
                    }

                    // Show loading overlay
                    showLoadingOverlay();
                });
            }

            // Smooth scroll to permissions section
            const permissionsSection = document.querySelector('.permissions-tabs');
            if (permissionsSection) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('animate-slide-in');
                        }
                    });
                });
                observer.observe(permissionsSection);
            }
        });

        // Utility functions
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

            document.body.appendChild(notification);

            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 3000);
        }

        function showLoadingOverlay() {
            const overlay = document.createElement('div');
            overlay.className = 'loading-overlay';
            overlay.innerHTML = '<div class=\"loading-spinner\"></div>';
            document.body.appendChild(overlay);
        }
    </script>
@endpush

@push('styles')
    <style>
        /* ========== ROLE CREATION INTERFACE STYLING ========== */

        /* Container and Layout */
        .role-create-container {
            background: linear-gradient(135deg, #f8f9fe 0%, #ffffff 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }

        /* Page Header Enhancements */
        .app-page-head {
            background: linear-gradient(135deg, #4361ee 0%, #3451dc 100%);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(67, 97, 238, 0.15);
        }

        .app-page-head .app-page-title {
            color: white;
            margin-bottom: 0.5rem;
            font-size: 1.8rem;
            font-weight: 700;
        }

        .app-page-head .breadcrumb-item a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
        }

        .app-page-head .breadcrumb-item.active {
            color: white;
        }

        /* Enhanced Card Styling */
        .role-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(67, 97, 238, 0.1);
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .role-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(67, 97, 238, 0.15);
        }

        .role-card .card-header {
            background: linear-gradient(135deg, #f8f9fe 0%, #eef2ff 100%);
            border-bottom: 2px solid #e4e7f0;
            padding: 1.5rem;
        }

        .role-card .card-header .card-title {
            color: #2d3561;
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .role-card .card-body {
            padding: 1.5rem;
        }

        /* Form Elements Enhancement */
        .form-group-enhanced {
            margin-bottom: 1.5rem;
        }

        .form-label-enhanced {
            color: #2d3561;
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .form-control-enhanced {
            border: 2px solid #e4e7f0;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control-enhanced:focus {
            border-color: #4361ee;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .form-select-enhanced {
            border: 2px solid #e4e7f0;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><path fill="none" stroke="%23343a40" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2 5l6 6 6-6"/></svg>');
        }

        /* Permissions Tabs Styling */
        .permissions-tabs .nav-tabs {
            border-bottom: 2px solid #e4e7f0;
            padding: 0 1rem;
        }

        .permissions-tabs .nav-link {
            border: none;
            border-bottom: 3px solid transparent;
            color: #6b7280;
            font-weight: 600;
            padding: 1rem 1.5rem;
            transition: all 0.3s ease;
            position: relative;
        }

        .permissions-tabs .nav-link:hover {
            color: #4361ee;
            background-color: rgba(67, 97, 238, 0.05);
        }

        .permissions-tabs .nav-link.active {
            color: #4361ee;
            background-color: rgba(67, 97, 238, 0.1);
            border-bottom-color: #4361ee;
        }

        .permissions-tabs .nav-link .badge {
            font-size: 0.75rem;
            margin-left: 0.5rem;
        }

        /* Permission Items Grid */
        .permissions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .permission-item {
            background: white;
            border: 2px solid #e4e7f0;
            border-radius: 10px;
            padding: 1rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .permission-item:hover {
            border-color: #4361ee;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.1);
        }

        .permission-item.selected {
            border-color: #10b981;
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        }

        .permission-name {
            color: #2d3561;
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 0.25rem;
        }

        .permission-description {
            color: #6b7280;
            font-size: 0.85rem;
            line-height: 1.4;
            margin: 0;
        }

        /* Custom Checkbox Styling */
        .form-check-input-custom {
            width: 1.2rem;
            height: 1.2rem;
            border: 2px solid #cbd0dd;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .form-check-input-custom:checked {
            background-color: #4361ee;
            border-color: #4361ee;
            background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>');
        }

        .form-check-input-custom:focus {
            border-color: #4361ee;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        /* Action Buttons */
        .btn-primary-enhanced {
            background: linear-gradient(135deg, #4361ee 0%, #3451dc 100%);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(67, 97, 238, 0.3);
        }

        .btn-primary-enhanced:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
            background: linear-gradient(135deg, #3451dc 0%, #2a42c7 100%);
        }

        .btn-outline-enhanced {
            border: 2px solid #e4e7f0;
            color: #6b7280;
            background: white;
            border-radius: 8px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .btn-outline-enhanced:hover {
            border-color: #4361ee;
            color: #4361ee;
            transform: translateY(-2px);
        }

        /* Role Color Preview */
        .color-preview {
            width: 30px;
            height: 30px;
            border-radius: 6px;
            border: 2px solid #e4e7f0;
            display: inline-block;
            vertical-align: middle;
            margin-left: 0.5rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .role-create-container {
                padding: 1rem 0;
            }

            .app-page-head {
                padding: 1.5rem;
                margin-bottom: 1.5rem;
            }

            .permissions-grid {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }

            .permission-item {
                padding: 0.75rem;
            }
        }

        /* Animation Keyframes */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-slide-in {
            animation: slideIn 0.6s ease-out;
        }

        /* Loading States */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #e4e7f0;
            border-top: 4px solid #4361ee;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Footer spacing fix */
        .app-wrapper {
            min-height: calc(100vh - 200px);
            padding-bottom: 2rem;
        }

        .container.pb-5.mb-5 {
            margin-bottom: 5rem !important;
            padding-bottom: 3rem !important;
        }
    </style>
@endpush
