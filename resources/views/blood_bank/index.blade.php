@extends('backend.layouts.master')

@section('title', 'Blood Bank Status')

@push('styles')
    <style>
        .bb-header {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            padding: 20px 24px 16px;
            border-bottom: 1px solid #e9ecef;
        }

        .bb-header h4 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 2px;
            color: #1a1a2e;
        }

        .bb-header p {
            color: #6c757d;
            font-size: 0.875rem;
            margin: 0;
        }

        .bb-header .btn-group-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .bb-header .btn-group-actions .btn {
            font-size: 0.8rem;
            border-radius: 20px;
            padding: 6px 16px;
            font-weight: 500;
        }

        /* Section panels */
        .bb-section {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            overflow: hidden;
        }

        .bb-section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            border-bottom: 1px solid #e9ecef;
        }

        .bb-section-header h5 {
            font-size: 1.2rem;
            font-weight: 700;
            margin: 0;
            color: #1a1a2e;
        }

        .bb-total-badge {
            background: #d4edda;
            color: #155724;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 20px;
            margin-left: 10px;
        }

        .bb-add-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 16px;
            border-radius: 20px;
            border: none;
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: #fff;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all .25s ease;
            box-shadow: 0 2px 6px rgba(220, 53, 69, 0.3);
        }

        .bb-add-btn:hover {
            background: linear-gradient(135deg, #c82333, #a71d2a);
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.45);
            transform: translateY(-1px);
        }

        .bb-add-btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(220, 53, 69, 0.25);
        }

        /* Blood group tabs (left sidebar) */
        .bb-blood-layout {
            display: flex;
            min-height: 300px;
        }

        .bb-group-sidebar {
            width: 100px;
            min-width: 100px;
            border-right: 1px solid #e9ecef;
            display: flex;
            flex-direction: column;
        }

        .bb-group-tab {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 14px 8px;
            border: none;
            border-bottom: 1px solid #f0f0f0;
            background: transparent;
            cursor: pointer;
            transition: all .2s;
        }

        .bb-group-tab:last-child {
            border-bottom: none;
        }

        .bb-group-tab:hover {
            background: #f8f9fa;
        }

        .bb-group-tab.active {
            background: #0d6efd;
            color: #fff;
        }

        .bb-group-tab .bb-gt-label {
            font-weight: 700;
            font-size: 1.1rem;
        }

        .bb-group-tab .bb-gt-count {
            font-size: 0.7rem;
            opacity: 0.7;
            margin-top: 2px;
        }

        .bb-group-tab.active .bb-gt-count {
            opacity: 0.9;
        }

        /* Collection list (right panel) */
        .bb-collection-panel {
            flex: 1;
            min-width: 0;
            overflow-x: auto;
        }

        .bb-collection-list {
            display: none;
        }

        .bb-collection-list.active {
            display: block;
        }

        /* Table inside group */
        .bb-table {
            width: 100%;
            font-size: 0.82rem;
        }

        .bb-table thead th {
            background: #f8f9fa;
            border: none;
            padding: 10px 12px;
            font-weight: 600;
            color: #999;
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 0.5px;
            position: sticky;
            top: 0;
        }

        .bb-table tbody td {
            padding: 10px 12px;
            vertical-align: middle;
            border-top: 1px solid #f5f5f5;
            color: #333;
        }

        .bb-table tbody tr:hover {
            background: #f8f9fa;
        }

        .bb-bag-vol {
            color: #999;
            font-size: 0.75rem;
        }

        .bb-lot {
            font-size: 0.8rem;
            color: #555;
        }

        /* Issue button */
        .btn-issue {
            background: #0d6efd;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 4px 14px;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-issue:hover {
            background: #0b5ed7;
            color: #fff;
        }

        /* Component badges */
        .component-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            background: #21bd24;
            color: #fff;
        }

        /* Empty state */
        .bb-blood-layout .text-muted {
            font-size: 0.85rem;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid p-0">
        <div class="card border-0 shadow-sm rounded-0">

            {{-- Header --}}
            <div class="bb-header">
                <div>
                    <h4>Blood Bank Status</h4>
                    <p>Real-time inventory and component tracking</p>
                </div>
                <div class="btn-group-actions">
                    <a target="_blank" href="{{ route('bb.blood-donors.index') }}" class="btn btn-outline-secondary">
                        <i class="fi fi-rr-user me-1"></i> Donor Details
                    </a>
                    <a href="{{ route('blood-issues.index', ['type' => 'blood']) }}" class="btn btn-outline-danger">
                        <i class="fi fi-rr-blood me-1"></i> Blood Issue Details
                    </a>
                    <a href="{{ route('blood-issues.index', ['type' => 'component']) }}" class="btn btn-outline-primary">
                        <i class="fi fi-rr-flask me-1"></i> Component Issue
                    </a>
                </div>
            </div>

            {{-- Body --}}
            <div class="card-body p-3">
                <div class="row g-3">

                    {{-- ======== LEFT: Blood Units ======== --}}
                    <div class="col-lg-6">
                        <div class="bb-section">
                            <div class="bb-section-header">
                                <div class="d-flex align-items-center">
                                    <h5>Blood</h5>
                                    <span class="bb-total-badge">{{ $bloodTotal }} Bags Total</span>
                                </div>
                                <button class="bb-add-btn" title="Add Blood Collection" data-bs-toggle="modal"
                                    data-bs-target="#bloodCollectionModal" data-mode="create"><i
                                        class="fi fi-rr-plus-small"></i> Add Collection</button>
                            </div>

                            <div class="bb-blood-layout">
                                {{-- Blood Group Tabs --}}
                                <div class="bb-group-sidebar">
                                    @foreach ($groupSummary as $i => $gs)
                                        <button type="button" class="bb-group-tab {{ $i === 0 ? 'active' : '' }}"
                                            data-target="bg-panel-{{ $gs['id'] }}">
                                            <span class="bb-gt-label">{{ $gs['display'] }}</span>
                                            <span class="bb-gt-count">{{ $gs['count'] }} bags</span>
                                        </button>
                                    @endforeach
                                </div>

                                {{-- Collection Lists --}}
                                <div class="bb-collection-panel">
                                    @foreach ($groupSummary as $i => $gs)
                                        <div class="bb-collection-list {{ $i === 0 ? 'active' : '' }}"
                                            id="bg-panel-{{ $gs['id'] }}">
                                            @php $items = $collectionsByGroup->get($gs['id'], collect()); @endphp
                                            @if ($items->count() > 0)
                                                <table class="bb-table">
                                                    <thead>
                                                        <tr>
                                                            <th>Bag No</th>
                                                            <th>Donor</th>
                                                            <th>Volume</th>
                                                            <th>Lot</th>
                                                            <th>Donate Date</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($items as $col)
                                                            <tr>
                                                                <td><strong>{{ $col->bag_no }}</strong></td>
                                                                <td>{{ $col->donor->name ?? '-' }}</td>
                                                                <td>{{ number_format($col->volume, 0) }} <span
                                                                        class="bb-bag-vol">{{ $col->unit }}</span></td>
                                                                <td class="bb-lot">{{ $col->lot ?? '-' }}</td>
                                                                <td>{{ $col->donate_date->format('d M Y, h:i A') }}</td>
                                                                <td><button class="btn-issue" data-type="blood" data-id="{{ $col->id }}" data-bag="{{ $col->bag_no }}" data-bs-toggle="modal" data-bs-target="#bloodIssueModal">Issue</button></td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            @else
                                                <div
                                                    class="d-flex align-items-center justify-content-center h-100 text-muted py-5">
                                                    No collections for {{ $gs['display'] }}
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- ======== RIGHT: Components ======== --}}
                    <div class="col-lg-6">
                        <div class="bb-section">
                            <div class="bb-section-header">
                                <div class="d-flex align-items-center">
                                    <h5>Components</h5>
                                    <span class="bb-total-badge">{{ $componentTotal ?? 0 }} Bags Total</span>
                                </div>
                                <button class="bb-add-btn" title="Add Component" data-bs-toggle="modal"
                                    data-bs-target="#componentCollectionModal">
                                    <i class="fi fi-rr-plus-small"></i> Add Component
                                </button>
                            </div>

                            <div class="bb-blood-layout">
                                {{-- Blood Group Tabs --}}
                                <div class="bb-group-sidebar">
                                    @foreach ($componentGroupSummary as $i => $cgs)
                                        <button type="button" class="bb-group-tab comp-group-tab {{ $i === 0 ? 'active' : '' }}"
                                            data-target="comp-panel-{{ $cgs['id'] }}">
                                            <span class="bb-gt-label">{{ $cgs['display'] }}</span>
                                            <span class="bb-gt-count">{{ $cgs['count'] }} bags</span>
                                        </button>
                                    @endforeach
                                </div>

                                {{-- Component Lists --}}
                                <div class="bb-collection-panel">
                                    @foreach ($componentGroupSummary as $i => $cgs)
                                        <div class="bb-collection-list {{ $i === 0 ? 'active' : '' }}"
                                            id="comp-panel-{{ $cgs['id'] }}">
                                            @php $compItems = $componentsByGroup->get($cgs['id'], collect()); @endphp
                                            @if ($compItems->count() > 0)
                                                <table class="bb-table">
                                                    <thead>
                                                        <tr>
                                                            <th>Bag No (Vol)</th>
                                                            <th>Lot</th>
                                                            <th>Component</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($compItems as $cc)
                                                            <tr>
                                                                <td>
                                                                    <span class="bb-bag-count">{{ $cc->component_bag_no }}</span>
                                                                    <div class="bb-bag-vol">({{ number_format($cc->volume, 0) }} {{ $cc->unit }})</div>
                                                                </td>
                                                                <td class="bb-lot">{{ $cc->lot ?? '-' }}</td>
                                                                <td>
                                                                    <span class="component-badge">
                                                                        {{ $cc->component->component_name ?? '-' }}
                                                                    </span>
                                                                </td>
                                                                <td><button class="btn-issue" data-type="component" data-id="{{ $cc->id }}" data-bag="{{ $cc->component_bag_no }}" data-bs-toggle="modal" data-bs-target="#bloodIssueModal">Issue</button></td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            @else
                                                <div class="d-flex align-items-center justify-content-center h-100 text-muted py-5">
                                                    No components for {{ $cgs['display'] }}
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
    {{-- ============ Blood Collection Modal ============ --}}
    <div class="modal fade" id="bloodCollectionModal" tabindex="-1" aria-labelledby="bloodCollectionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="bloodCollectionForm" method="POST" action="{{ route('blood-collections.store') }}">
                @csrf
                <input type="hidden" name="_method" id="bcFormMethod" value="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="bloodCollectionModalLabel">Add Blood Collection</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">

                            {{-- Donor --}}
                            <div class="col-md-6">
                                <label for="bcDonor" class="form-label">Donor <span class="text-danger">*</span></label>
                                <select name="donor_id" id="bcDonor" class="form-select" required>
                                    <option value="">-- Select Donor --</option>
                                    @foreach ($donors as $donor)
                                        <option value="{{ $donor->id }}"
                                            data-blood-group="{{ $donor->blood_group_id }}">
                                            {{ $donor->donor_code }} - {{ $donor->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Blood Group --}}
                            <div class="col-md-6">
                                <label for="bcBloodGroup" class="form-label">Blood Group <span
                                        class="text-danger">*</span></label>
                                <select name="blood_group_id" id="bcBloodGroup" class="form-select" required>
                                    <option value="">-- Select Blood Group --</option>
                                    @foreach ($allBloodGroups as $bg)
                                        <option value="{{ $bg->id }}">{{ $bg->combined }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Donate Date --}}
                            <div class="col-md-4">
                                <label for="bcDonateDate" class="form-label">Donate Date <span
                                        class="text-danger">*</span></label>
                                <input type="datetime-local" name="donate_date" id="bcDonateDate" class="form-control"
                                    value="{{ date('Y-m-d\TH:i') }}" required>
                            </div>

                            {{-- Bag No --}}
                            <div class="col-md-4">
                                <label for="bcBagNo" class="form-label">Bag No</label>
                                <input type="text" name="bag_no" id="bcBagNo" class="form-control"
                                    placeholder="Auto-generated if empty">
                            </div>

                            {{-- Lot --}}
                            <div class="col-md-4">
                                <label for="bcLot" class="form-label">Lot</label>
                                <input type="text" name="lot" id="bcLot" class="form-control"
                                    placeholder="Lot number">
                            </div>

                            {{-- Volume --}}
                            <div class="col-md-4">
                                <label for="bcVolume" class="form-label">Volume <span
                                        class="text-danger">*</span></label>
                                <input type="number" name="volume" id="bcVolume" class="form-control" step="0.01"
                                    min="0" placeholder="e.g. 450" required>
                            </div>

                            {{-- Unit --}}
                            <div class="col-md-4">
                                <label for="bcUnit" class="form-label">Unit <span class="text-danger">*</span></label>
                                <select name="unit" id="bcUnit" class="form-select" required>
                                    <option value="ML" selected>ML</option>
                                    <option value="L">L</option>
                                    <option value="Unit">Unit</option>
                                </select>
                            </div>

                            {{-- Charge --}}
                            <div class="col-md-4">
                                <label for="bcCharge" class="form-label">Charge</label>
                                <select name="charge_id" id="bcCharge" class="form-select">
                                    <option value="">-- None --</option>
                                    @foreach ($charges as $ch)
                                        <option value="{{ $ch->id }}" data-name="{{ $ch->charge_name }}">
                                            {{ $ch->charge_name }} ({{ number_format($ch->standard_charge, 2) }})
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="charge_name" id="bcChargeName">
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Collection</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    {{-- ============ Component Collection Modal ============ --}}
    <div class="modal fade" id="componentCollectionModal" tabindex="-1" aria-labelledby="componentCollectionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form id="componentCollectionForm" method="POST" action="{{ route('component-collections.store') }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="componentCollectionModalLabel">Add Components</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3 mb-4">
                            {{-- Blood Group --}}
                            <div class="col-md-4">
                                <label for="ccBloodGroup" class="form-label">Blood Group <span
                                        class="text-danger">*</span></label>
                                <select name="blood_group_id" id="ccBloodGroup" class="form-select" required>
                                    <option value="">-- Select --</option>
                                    @foreach ($allBloodGroups as $bg)
                                        <option value="{{ $bg->id }}">{{ $bg->combined }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Bag (Blood Collection) --}}
                            <div class="col-md-4">
                                <label for="ccBloodCollection" class="form-label">Bag <span
                                        class="text-danger">*</span></label>
                                <select name="blood_collection_id" id="ccBloodCollection" class="form-select" required>
                                    <option value="">-- Select Bag --</option>
                                </select>
                                <input type="hidden" name="donor_id" id="ccDonorId">
                            </div>

                            {{-- DateTime --}}
                            <div class="col-md-4">
                                <label for="ccDatetime" class="form-label">Date & Time <span
                                        class="text-danger">*</span></label>
                                <input type="datetime-local" name="datetime" id="ccDatetime" class="form-control"
                                    value="{{ date('Y-m-d\TH:i') }}" required>
                            </div>
                        </div>

                        {{-- Components Table --}}
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0" id="ccComponentsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:30px"></th>
                                        <th>Components Name</th>
                                        <th>Volume</th>
                                        <th>Unit</th>
                                        <th>Lot</th>
                                        <th>Institution</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($allComponents as $comp)
                                        <tr>
                                            <td class="text-center">
                                                <input type="checkbox" class="form-check-input cc-check"
                                                    data-index="{{ $loop->index }}" value="{{ $comp->id }}">
                                                <input type="hidden" name="components[{{ $loop->index }}][component_id]"
                                                    value="{{ $comp->id }}" disabled>
                                            </td>
                                            <td>{{ $comp->component_name }}</td>
                                            <td>
                                                <input type="number" name="components[{{ $loop->index }}][volume]"
                                                    class="form-control form-control-sm" step="0.01" min="0"
                                                    placeholder="0.0" disabled>
                                            </td>
                                            <td>
                                                <select name="components[{{ $loop->index }}][unit]"
                                                    class="form-select form-select-sm" disabled>
                                                    <option value="ML" selected>ML</option>
                                                    <option value="L">L</option>
                                                    <option value="Unit">Unit</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" name="components[{{ $loop->index }}][lot]"
                                                    class="form-control form-control-sm" placeholder="Lot #" disabled>
                                            </td>
                                            <td>
                                                <input type="text" name="components[{{ $loop->index }}][institution]"
                                                    class="form-control form-control-sm" placeholder="Central Clinic"
                                                    disabled>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning fw-bold">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    {{-- ============ Blood / Component Issue Modal ============ --}}
    <div class="modal fade" id="bloodIssueModal" tabindex="-1" aria-labelledby="bloodIssueModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="bloodIssueForm" method="POST" action="{{ route('blood-issues.store') }}">
                @csrf
                <input type="hidden" name="type" id="biType">
                <input type="hidden" name="blood_collection_id" id="biBloodCollectionId">
                <input type="hidden" name="component_collection_id" id="biComponentCollectionId">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="bloodIssueModalLabel">Issue Blood</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @if ($errors->any())
                            <div class="alert alert-danger py-2 mb-3">
                                <ul class="mb-0 small">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="row g-3">
                            {{-- Bag Info (read-only) --}}
                            <div class="col-md-6">
                                <label class="form-label">Bag No</label>
                                <input type="text" id="biBagNo" class="form-control" readonly>
                            </div>

                            {{-- Patient --}}
                            <div class="col-md-6">
                                <label for="biPatient" class="form-label">Patient <span class="text-danger">*</span></label>
                                <select name="patient_id" id="biPatient" class="form-select" required>
                                    <option value="">-- Select Patient --</option>
                                    @foreach ($patients as $pt)
                                        <option value="{{ $pt->id }}">{{ $pt->mrn }} - {{ $pt->patient_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Case ID --}}
                            <div class="col-md-6">
                                <label for="biCase" class="form-label">Case ID</label>
                                <input type="number" name="case_id" id="biCase" class="form-control" placeholder="Case reference ID">
                            </div>

                            {{-- Issue DateTime --}}
                            <div class="col-md-6">
                                <label for="biDatetime" class="form-label">Issue Date & Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="issue_datetime" id="biDatetime" class="form-control" value="{{ date('Y-m-d\TH:i') }}" required>
                            </div>

                            {{-- Doctor --}}
                            <div class="col-md-6">
                                <label for="biDoctor" class="form-label">Doctor</label>
                                <select name="doctor_id" id="biDoctor" class="form-select">
                                    <option value="">-- Select Doctor --</option>
                                    @foreach ($doctors as $doc)
                                        <option value="{{ $doc->id }}">{{ $doc->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Reference Name --}}
                            <div class="col-md-6">
                                <label for="biReference" class="form-label">Reference Name</label>
                                <input type="text" name="reference_name" id="biReference" class="form-control" placeholder="Reference name">
                            </div>

                            {{-- Technician Name --}}
                            <div class="col-md-6">
                                <label for="biTechnician" class="form-label">Technician Name</label>
                                <input type="text" name="technician_name" id="biTechnician" class="form-control" placeholder="Technician name">
                            </div>

                            {{-- Charge --}}
                            <div class="col-md-6">
                                <label for="biCharge" class="form-label">Charge</label>
                                <select name="charge_id" id="biCharge" class="form-select">
                                    <option value="">-- None --</option>
                                    @foreach ($charges as $ch)
                                        <option value="{{ $ch->id }}">{{ $ch->charge_name }} ({{ number_format($ch->standard_charge, 2) }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Issue</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('bloodCollectionModal');
            const form = document.getElementById('bloodCollectionForm');
            const methodFld = document.getElementById('bcFormMethod');
            const titleEl = document.getElementById('bloodCollectionModalLabel');
            const storeUrl = "{{ route('blood-collections.store') }}";

            // Auto-fill blood group when donor is selected
            const donorSel = document.getElementById('bcDonor');
            const bgSel = document.getElementById('bcBloodGroup');

            donorSel.addEventListener('change', function() {
                const opt = this.options[this.selectedIndex];
                const bgId = opt.getAttribute('data-blood-group');
                if (bgId) {
                    bgSel.value = bgId;
                }
            });

            // Auto-fill charge_name hidden field
            const chargeSel = document.getElementById('bcCharge');
            const chargeNameFld = document.getElementById('bcChargeName');

            chargeSel.addEventListener('change', function() {
                const opt = this.options[this.selectedIndex];
                chargeNameFld.value = opt.getAttribute('data-name') || '';
            });

            // Blood group tab switching (scoped to parent layout)
            document.querySelectorAll('.bb-group-tab:not(.comp-group-tab)').forEach(function(tab) {
                tab.addEventListener('click', function() {
                    const parent = this.closest('.bb-blood-layout');
                    parent.querySelectorAll('.bb-group-tab').forEach(t => t.classList.remove('active'));
                    parent.querySelectorAll('.bb-collection-list').forEach(p => p.classList.remove('active'));
                    this.classList.add('active');
                    const panel = document.getElementById(this.getAttribute('data-target'));
                    if (panel) panel.classList.add('active');
                });
            });

            // Component blood group tab switching
            document.querySelectorAll('.comp-group-tab').forEach(function(tab) {
                tab.addEventListener('click', function() {
                    document.querySelectorAll('.comp-group-tab').forEach(t => t.classList.remove('active'));
                    const parent = this.closest('.bb-blood-layout');
                    parent.querySelectorAll('.bb-collection-list').forEach(p => p.classList.remove('active'));
                    this.classList.add('active');
                    const panel = document.getElementById(this.getAttribute('data-target'));
                    if (panel) panel.classList.add('active');
                });
            });

            // Reset form on modal open for create mode
            modal.addEventListener('show.bs.modal', function(e) {
                const trigger = e.relatedTarget;
                const mode = trigger ? trigger.getAttribute('data-mode') : 'create';

                if (mode === 'create') {
                    form.reset();
                    form.action = storeUrl;
                    methodFld.value = 'POST';
                    titleEl.textContent = 'Add Blood Collection';
                    document.getElementById('bcDonateDate').value = "{{ date('Y-m-d\TH:i') }}";
                }
            });

            // ===== Component Collection Modal Logic =====
            const ccModal = document.getElementById('componentCollectionModal');
            const ccForm = document.getElementById('componentCollectionForm');
            const ccBgSel = document.getElementById('ccBloodGroup');
            const ccBagSel = document.getElementById('ccBloodCollection');
            const ccDonorId = document.getElementById('ccDonorId');

            // Blood collections data for filtering by blood group
            const allCollections = @json($collections);

            // Filter bags when blood group changes
            ccBgSel.addEventListener('change', function() {
                const bgId = parseInt(this.value);
                ccBagSel.innerHTML = '<option value="">-- Select Bag --</option>';
                ccDonorId.value = '';

                if (!bgId) return;

                allCollections.filter(c => c.blood_group_id === bgId).forEach(function(c) {
                    const opt = document.createElement('option');
                    opt.value = c.id;
                    opt.textContent = c.bag_no + (c.donor ? ' - ' + c.donor.name : '');
                    opt.setAttribute('data-donor-id', c.donor_id);
                    ccBagSel.appendChild(opt);
                });
            });

            // Auto-fill donor_id when bag is selected
            ccBagSel.addEventListener('change', function() {
                const opt = this.options[this.selectedIndex];
                ccDonorId.value = opt ? opt.getAttribute('data-donor-id') || '' : '';
            });

            // Toggle component row inputs when checkbox is checked/unchecked
            document.querySelectorAll('.cc-check').forEach(function(cb) {
                cb.addEventListener('change', function() {
                    const row = this.closest('tr');
                    const inputs = row.querySelectorAll(
                        'input[type="text"], input[type="number"], input[type="hidden"], select'
                        );
                    inputs.forEach(function(inp) {
                        inp.disabled = !cb.checked;
                    });
                });
            });

            // Reset component modal on open
            ccModal.addEventListener('show.bs.modal', function() {
                ccForm.reset();
                ccBagSel.innerHTML = '<option value="">-- Select Bag --</option>';
                ccDonorId.value = '';
                document.getElementById('ccDatetime').value = "{{ date('Y-m-d\TH:i') }}";
                document.querySelectorAll('.cc-check').forEach(function(cb) {
                    cb.checked = false;
                    const row = cb.closest('tr');
                    row.querySelectorAll(
                        'input[type="text"], input[type="number"], input[type="hidden"], select'
                        ).forEach(function(inp) {
                        inp.disabled = true;
                    });
                });
            });

            // On submit, remove disabled inputs so only checked components are sent
            ccForm.addEventListener('submit', function(e) {
                const checked = document.querySelectorAll('.cc-check:checked');
                if (checked.length === 0) {
                    e.preventDefault();
                    alert('Please select at least one component.');
                }
            });

            // ===== Blood / Component Issue Modal Logic =====
            const issueModal = document.getElementById('bloodIssueModal');

            issueModal.addEventListener('show.bs.modal', function(e) {
                const btn = e.relatedTarget;
                if (!btn) return;

                const type = btn.getAttribute('data-type');
                const id   = btn.getAttribute('data-id');
                const bag  = btn.getAttribute('data-bag');

                document.getElementById('biType').value = type;
                document.getElementById('biBagNo').value = bag;
                document.getElementById('biDatetime').value = "{{ date('Y-m-d\TH:i') }}";

                // Reset hidden ids
                document.getElementById('biBloodCollectionId').value = '';
                document.getElementById('biComponentCollectionId').value = '';

                if (type === 'blood') {
                    document.getElementById('biBloodCollectionId').value = id;
                    document.getElementById('bloodIssueModalLabel').textContent = 'Issue Blood';
                } else {
                    document.getElementById('biComponentCollectionId').value = id;
                    document.getElementById('bloodIssueModalLabel').textContent = 'Issue Component';
                }

                // Reset other fields
                document.getElementById('biPatient').value = '';
                document.getElementById('biCase').value = '';
                document.getElementById('biDoctor').value = '';
                document.getElementById('biReference').value = '';
                document.getElementById('biTechnician').value = '';
                document.getElementById('biCharge').value = '';
            });

            // Auto-open modal if there are validation errors
            @if ($errors->any())
                new bootstrap.Modal(issueModal).show();
            @endif
        });
    </script>
@endpush
