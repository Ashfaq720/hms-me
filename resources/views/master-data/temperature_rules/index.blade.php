@extends('backend.layouts.master')
@section('title', 'Temperature Rules')

@section('content')
    <div class="container-fluid">
        <div class="row">
            {{-- LEFT --}}
            <div class="col-lg-3 col-md-4 mb-3">
                @include('backend.layouts.bloodbank_setup')
            </div>

            {{-- RIGHT --}}
            <div class="col-lg-9 col-md-8">
                <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
                    <div class="clearfix">
                        <h1 class="app-page-title">Component Temperature Rules</h1>
                    </div>
                    <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal"
                        data-bs-target="#bbTempRuleModal" data-mode="create" data-title="Create Temperature Rule"
                        data-action="{{ route('bb.temperature-rules.store') }}">
                        <i class="fi fi-rr-plus me-1"></i> Add Rule
                    </button>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card overflow-hidden">
                            <div
                                class="card-header d-flex flex-wrap gap-3 align-items-center justify-content-between border-0 pb-0">
                                <h6 class="card-title mb-2">Temperature Rule List</h6>
                                <div id="dt_bb_temp_rules_Search"></div>
                            </div>

                            <div class="card-body px-3 pt-2 pb-0 gradient-layer">
                                <table id="dt_bb_temp_rules" class="table display table-row-rounded">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Component</th>
                                            <th>Min (°C)</th>
                                            <th>Max (°C)</th>
                                            <th>Monitoring</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse($items as $row)
                                            <tr>
                                                <td>{{ $row->id }}</td>
                                                <td class="fw-bold">{{ $row->component->component_name ?? '-' }}</td>
                                                <td>{{ $row->min_temp }}</td>
                                                <td>{{ $row->max_temp }}</td>
                                                <td>
                                                    @if ($row->monitoring_required)
                                                        <span class="badge bg-info text-dark">Required</span>
                                                    @else
                                                        <span class="badge bg-secondary">No</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($row->is_active)
                                                        <span class="badge bg-success">Yes</span>
                                                    @else
                                                        <span class="badge bg-secondary">No</span>
                                                    @endif
                                                </td>

                                                <td class="text-nowrap">
                                                    <div class="d-flex flex-wrap gap-1 justify-content-start">
                                                        <a href="javascript:void(0)" class="btn btn-sm btn-warning"
                                                            data-bs-toggle="modal" data-bs-target="#bbTempRuleModal"
                                                            data-mode="edit" data-title="Edit Temperature Rule"
                                                            data-action="{{ route('bb.temperature-rules.update', $row->id) }}"
                                                            data-component_id="{{ $row->component_id }}"
                                                            data-min="{{ $row->min_temp }}"
                                                            data-max="{{ $row->max_temp }}"
                                                            data-monitor="{{ $row->monitoring_required ? 1 : 0 }}"
                                                            data-active="{{ $row->is_active ? 1 : 0 }}" title="Edit">
                                                            <i class="fa-solid fa-pen-to-square"></i>
                                                        </a>

                                                        <form method="POST"
                                                            action="{{ route('bb.temperature-rules.destroy', $row->id) }}"
                                                            onsubmit="return confirm('Set inactive?')" class="m-0">
                                                            @csrf @method('DELETE')
                                                            <button class="btn btn-sm btn-danger" type="submit">
                                                                <i class="fa-solid fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center">No data found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>

                                <div class="mt-3 pb-3">
                                    {{ $items->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="bbTempRuleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header d-flex align-items-center">
                    <h5 class="modal-title" id="bbTempRuleModalTitle">Temperature Rule</h5>
                </div>

                <form id="bbTempRuleForm" method="POST" action="{{ route('bb.temperature-rules.store') }}">
                    @csrf
                    <input type="hidden" id="bbTempRuleMethod" value="">

                    <div class="modal-body">
                        @include('master-data.temperature_rules._form', [
                            'item' => null,
                            'components' => $components,
                        ])
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="bbTempRuleSubmitBtn">Save</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modalEl = document.getElementById('bbTempRuleModal');
            const titleEl = document.getElementById('bbTempRuleModalTitle');
            const formEl = document.getElementById('bbTempRuleForm');
            const submitBtn = document.getElementById('bbTempRuleSubmitBtn');
            const methodInp = document.getElementById('bbTempRuleMethod');

            const componentInp = document.getElementById('bb_tr_component_id');
            const minInp = document.getElementById('bb_tr_min');
            const maxInp = document.getElementById('bb_tr_max');
            const monitorInp = document.getElementById('bb_tr_monitor');
            const activeInp = document.getElementById('bb_tr_active');

            modalEl.addEventListener('show.bs.modal', function(event) {
                const trigger = event.relatedTarget;
                if (!trigger) return;

                const mode = trigger.getAttribute('data-mode');
                const title = trigger.getAttribute('data-title');
                const action = trigger.getAttribute('data-action');

                titleEl.innerText = title || 'Temperature Rule';
                formEl.action = action || formEl.action;

                if (mode === 'edit') {
                    methodInp.setAttribute('name', '_method');
                    methodInp.value = 'PUT';
                    submitBtn.innerText = 'Update';

                    componentInp.value = trigger.getAttribute('data-component_id') || '';
                    minInp.value = trigger.getAttribute('data-min') || '';
                    maxInp.value = trigger.getAttribute('data-max') || '';
                    monitorInp.value = trigger.getAttribute('data-monitor') || 1;
                    activeInp.value = trigger.getAttribute('data-active') || 1;

                    // Component cannot change after create
                    componentInp.setAttribute('disabled', 'disabled');
                } else {
                    methodInp.removeAttribute('name');
                    methodInp.value = '';
                    submitBtn.innerText = 'Save';

                    componentInp.value = '';
                    minInp.value = '';
                    maxInp.value = '';
                    monitorInp.value = 1;
                    activeInp.value = 1;

                    componentInp.removeAttribute('disabled');
                }
            });
        });
    </script>
@endpush
