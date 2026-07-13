@extends('backend.layouts.master')

@section('title', 'Health Card Management')

@section('content')
<div class="container">
    {{-- Page Header --}}
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <div class="clearfix">
            <h1 class="app-page-title mb-1">Health Card Management</h1>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mt-2 mb-3">
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width: 44px; height: 44px; background: var(--primary-bg, #EEF2FF);">
                        <i class="fas fa-id-card" style="font-size: 18px; color: var(--primary, #4361EE);"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold">{{ number_format($stats['total']) }}</h3>
                        <p class="text-muted mb-0" style="font-size: 13px;">Total Cards</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width: 44px; height: 44px; background: var(--success-bg, #D1FAE5);">
                        <i class="fas fa-check-circle" style="font-size: 18px; color: var(--success, #10B981);"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold">{{ number_format($stats['active']) }}</h3>
                        <p class="text-muted mb-0" style="font-size: 13px;">Active Cards</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width: 44px; height: 44px; background: var(--danger-bg, #FEE2E2);">
                        <i class="fas fa-ban" style="font-size: 18px; color: var(--danger, #EF4444);"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold">{{ number_format($stats['inactive']) }}</h3>
                        <p class="text-muted mb-0" style="font-size: 13px;">Inactive</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width: 44px; height: 44px; background: #E0F2FE;">
                        <i class="fas fa-calendar-day" style="font-size: 18px; color: #0284C7;"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold">{{ number_format($stats['today']) }}</h3>
                        <p class="text-muted mb-0" style="font-size: 13px;">Issued Today</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mt-3">
        <div class="card-body py-3">
            <form class="row g-2 align-items-end" method="GET" action="{{ route('health-card.index') }}" autocomplete="off">
                <div class="col-lg-4 col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search by card no, name, mobile..." value="{{ request('search') }}">
                </div>
                <div class="col-lg-2 col-md-3">
                    <select name="status" class="form-select">
                        <option value="" {{ request('status') == '' ? 'selected' : '' }}>All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3">
                    <input type="date" name="from_date" class="form-control" placeholder="From Date" value="{{ request('from_date') }}">
                </div>
                <div class="col-lg-2 col-md-3">
                    <input type="date" name="to_date" class="form-control" placeholder="To Date" value="{{ request('to_date') }}">
                </div>
                <div class="col-auto d-flex gap-1">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i>
                    </button>
                    <a href="{{ route('health-card.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-sync-alt"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="row mt-3">
        <div class="col-12">
            <div class="card overflow-hidden">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 fw-semibold">All Health Cards</h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-secondary" onclick="printTable()"><i class="fas fa-print me-1"></i> Print</button>
                    </div>
                </div>

                <div class="card-body px-2 pt-2 pb-0 gradient-layer" style="min-height: 400px;">
                    <table id="hcTable" class="table table-sm table-hover display table-row-rounded mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-2" style="width:35px;">SN</th>
                                <th style="width:130px;">Card No.</th>
                                <th>Patient Name</th>
                                <th style="width:130px;">Mobile No</th>
                                <th style="width:80px;">Gender</th>
                                <th style="width:150px;">Date of Birth</th>
                                <th style="width:150px;">Issue Date</th>
                                <th style="width:80px;">Status</th>
                                <th style="width:120px;" class="text-end pe-2">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($patients as $index => $p)
                                <tr class="hc-row">
                                    <td class="ps-2 text-muted">{{ $loop->iteration }}</td>

                                    <td>
                                        <span class="fw-semibold" style="color: var(--primary, #4361EE);">{{ $p->health_card_no ?? '-' }}</span>
                                    </td>

                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @php
                                                $words    = array_filter(explode(' ', $p->patient_name));
                                                $initials = strtoupper(
                                                    substr($words[array_key_first($words)] ?? '', 0, 1) .
                                                    substr($words[array_key_last($words)]  ?? '', 0, 1)
                                                );
                                                $palette  = ['#4361ee','#3a86ff','#06d6a0','#8338ec','#fb5607','#e07a5f','#3d405b','#2d6a4f'];
                                                $bgColor  = $palette[abs(crc32($p->patient_name)) % count($palette)];
                                            @endphp
                                            @if ($p->image)
                                                <img src="{{ asset('storage/' . $p->image) }}" width="40" height="40"
                                                    class="rounded-2 flex-shrink-0" style="object-fit:cover;">
                                            @else
                                                <div class="rounded-2 flex-shrink-0 d-flex align-items-center justify-content-center text-white fw-bold"
                                                    style="width:40px;height:40px;background:{{ $bgColor }};font-size:13px;letter-spacing:.5px">
                                                    {{ $initials }}
                                                </div>
                                            @endif
                                            <div>
                                                <div class="fw-semibold lh-sm">{{ $p->patient_name }}</div>
                                                <div class="text-muted" style="font-size: 11px;">{{ $p->mrn ?? '' }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <td>{{ $p->mobileno ?? '-' }}</td>

                                    <td>{{ $p->gender ?? '-' }}</td>

                                    <td>
                                        @if ($p->dob)
                                            {{ $p->dob->format('d M Y') }}
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td>
                                        @if ($p->created_at)
                                            {{ $p->created_at->format('d M Y') }}
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td>
                                        @if ($p->is_active)
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill">Active</span>
                                        @else
                                            <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill">Inactive</span>
                                        @endif
                                    </td>

                                    <td class="text-end pe-2">
                                        <div class="d-flex justify-content-end gap-1">
                                            <button class="btn btn-sm btn-info" title="View" data-bs-toggle="modal" data-bs-target="#patientModal{{ $p->id }}">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                            <a class="btn btn-sm btn-success" title="Health Card" href="{{ route('health-card.show', $p) }}" target="_blank">
                                                <i class="fa-solid fa-id-card"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        No patients found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if (count($patients))
                    <div class="card-footer bg-white border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex align-items-center gap-2 small text-muted">
                            <span>Rows per page:</span>
                            <select id="hcPerPage" class="form-select form-select-sm" style="width:auto;">
                                <option value="10">10</option>
                                <option value="15" selected>15</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                            <span id="hcRangeInfo"></span>
                        </div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0" id="hcPagination"></ul>
                        </nav>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Patient Info Modals --}}
@foreach($patients as $p)
    <div class="modal fade" id="patientModal{{ $p->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-circle-user me-1"></i> Personal Information
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-4">
                        {{-- Personal Info --}}
                        <div class="col-lg-6">
                            <p class="text-uppercase fw-bold text-muted mb-3" style="font-size:11px;letter-spacing:.8px">
                                <i class="fa-solid fa-circle-user me-1"></i> Personal Information
                            </p>
                            @php
                                $personalFields = [
                                    ['Full Name',       $p->patient_name],
                                    ['Gender',          ucfirst($p->gender ?? '')],
                                    ['Date of Birth',   $p->dob ? $p->dob->format('d M Y') . ' — ' . $p->dob->age . ' yrs' : null],
                                    ['Marital Status',  ucfirst($p->marital_status ?? '')],
                                    ['Blood Group',     $p->blood_group],
                                    ['Mobile',          $p->mobileno],
                                    ['Email',           $p->email],
                                    ['Address',         $p->address],
                                    ['Guardian',        $p->guardian_name],
                                ];
                            @endphp
                            <div class="d-flex flex-column gap-0">
                                @foreach($personalFields as [$label, $value])
                                    @if(!empty($value))
                                        <div class="d-flex gap-2 py-2 border-bottom">
                                            <span class="text-muted flex-shrink-0" style="width:140px;font-size:13px">{{ $label }}</span>
                                            <span class="fw-semibold" style="font-size:13.5px">{{ $value }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        {{-- Medical & Administrative --}}
                        <div class="col-lg-6">
                            <p class="text-uppercase fw-bold text-muted mb-3" style="font-size:11px;letter-spacing:.8px">
                                <i class="fa-solid fa-notes-medical me-1"></i> Medical & Administrative
                            </p>
                            @php
                                $medFields = [
                                    ['MRN',                $p->mrn],
                                    ['Health Card No',     $p->health_card_no],
                                    ['Patient Type',       $p->patient_type],
                                    ['Identification No',  $p->identification_number],
                                    ['Organization',       $p->organization_name],
                                    ['Insurance',          $p->insurance],
                                    ['Insurance Validity', $p->insurance_validity ? \Carbon\Carbon::parse($p->insurance_validity)->format('d M Y') : null],
                                    ['Known Allergies',    $p->known_allergies],
                                    ['Notes',              $p->note],
                                ];
                            @endphp
                            <div class="d-flex flex-column gap-0">
                                @foreach($medFields as [$label, $value])
                                    @if(!empty($value))
                                        <div class="d-flex gap-2 py-2 border-bottom">
                                            <span class="text-muted flex-shrink-0" style="width:140px;font-size:13px">{{ $label }}</span>
                                            <span class="fw-semibold" style="font-size:13.5px">{{ $value }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('patients.show', $p) }}" class="btn btn-sm btn-primary">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Full Profile
                    </a>
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endforeach

<script>
(function() {
    const rows = Array.from(document.querySelectorAll('.hc-row'));
    if (!rows.length) return;

    const perPageSel = document.getElementById('hcPerPage');
    const pagination = document.getElementById('hcPagination');
    const rangeInfo  = document.getElementById('hcRangeInfo');
    const tbody      = rows[0].parentNode;
    const colCount   = rows[0].children.length;

    const noMatchRow = document.createElement('tr');
    noMatchRow.id = 'hcNoMatchRow';
    noMatchRow.style.display = 'none';
    noMatchRow.innerHTML = '<td colspan="' + colCount + '" class="text-center text-muted py-4">No matching patients</td>';
    tbody.appendChild(noMatchRow);

    let currentPage = 1;

    function render() {
        const perPage    = parseInt(perPageSel.value, 10);
        const total      = rows.length;
        const totalPages = Math.max(1, Math.ceil(total / perPage));
        if (currentPage > totalPages) currentPage = totalPages;
        const start = (currentPage - 1) * perPage;
        const end   = Math.min(start + perPage, total);

        rows.forEach(r => { r.style.display = 'none'; });
        rows.slice(start, end).forEach(r => { r.style.display = ''; });
        noMatchRow.style.display = total === 0 ? '' : 'none';

        rangeInfo.textContent = 'Showing ' + (total ? start + 1 : 0) + '–' + end + ' of ' + total;

        let html = '';
        const mkItem = (label, page, disabled, active) =>
            '<li class="page-item ' + (disabled ? 'disabled' : '') + ' ' + (active ? 'active' : '') + '">' +
            '<a class="page-link" href="#" data-page="' + page + '">' + label + '</a></li>';

        html += mkItem('&laquo;', currentPage - 1, currentPage === 1, false);
        for (let p = 1; p <= totalPages; p++) {
            if (p === 1 || p === totalPages || Math.abs(p - currentPage) <= 1) {
                html += mkItem(p, p, false, p === currentPage);
            } else if (Math.abs(p - currentPage) === 2) {
                html += '<li class="page-item disabled"><span class="page-link">…</span></li>';
            }
        }
        html += mkItem('&raquo;', currentPage + 1, currentPage === totalPages, false);
        pagination.innerHTML = html;

        pagination.querySelectorAll('a.page-link').forEach(a => {
            a.addEventListener('click', e => {
                e.preventDefault();
                const p = parseInt(a.dataset.page, 10);
                if (!isNaN(p) && p >= 1 && p <= totalPages) {
                    currentPage = p;
                    render();
                }
            });
        });
    }

    perPageSel.addEventListener('change', () => { currentPage = 1; render(); });
    render();
})();

function printTable() {
    var table = document.getElementById('hcTable').cloneNode(true);

    // Show all rows for print
    table.querySelectorAll('.hc-row').forEach(function(r) { r.style.display = ''; });

    // Remove Action column (last column)
    table.querySelectorAll('tr').forEach(function(row) {
        var cells = row.querySelectorAll('th, td');
        if (cells.length > 0) {
            cells[cells.length - 1].remove();
        }
    });

    // Remove no-match row if present
    var noMatch = table.querySelector('#hcNoMatchRow');
    if (noMatch) noMatch.remove();

    var win = window.open('', '_blank');
    win.document.write('<html><head><title>Health Card List</title>');
    win.document.write('<style>');
    win.document.write('body { font-family: Arial, sans-serif; padding: 20px; }');
    win.document.write('h2 { text-align: center; margin-bottom: 20px; }');
    win.document.write('table { width: 100%; border-collapse: collapse; font-size: 13px; }');
    win.document.write('th, td { border: 1px solid #dee2e6; padding: 8px 10px; text-align: left; }');
    win.document.write('th { background: #f8f9fa; font-weight: 600; }');
    win.document.write('img { width: 30px; height: 30px; border-radius: 4px; }');
    win.document.write('.rounded-2 { width: 30px; height: 30px; border-radius: 4px; display: inline-flex; align-items: center; justify-content: center; color: #fff; font-size: 11px; font-weight: bold; }');
    win.document.write('@media print { body { padding: 0; } }');
    win.document.write('</style></head><body>');
    win.document.write('<h2>Health Card Patient List</h2>');
    win.document.write(table.outerHTML);
    win.document.write('</body></html>');
    win.document.close();
    win.print();
}
</script>
@endsection
