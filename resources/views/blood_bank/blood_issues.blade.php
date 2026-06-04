@extends('backend.layouts.master')

@section('title', 'Blood Issue Details')

@push('styles')
    <style>
        .bi-header {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 20px 24px 16px;
            border-bottom: 1px solid #e9ecef;
        }

        .bi-header h4 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 2px;
            color: #1a1a2e;
        }

        .bi-header p {
            color: #6c757d;
            font-size: 0.875rem;
            margin: 0;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid p-0">
        <div class="card border-0 shadow-sm rounded-0">

            {{-- Header --}}
            <div class="bi-header">
                <div>
                    <h4>Blood Issue Details</h4>
                    <p>List of all blood units issued to patients</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('blood-bank.index') }}" class="btn btn-outline-secondary" style="border-radius:20px;font-size:.8rem;padding:6px 16px;">
                        <i class="fi fi-rr-arrow-left me-1"></i> Back to Blood Bank
                    </a>
                    <a href="{{ route('blood-issues.index', ['type' => 'component']) }}" class="btn btn-outline-primary" style="border-radius:20px;font-size:.8rem;padding:6px 16px;">
                        <i class="fi fi-rr-flask me-1"></i> Component Issues
                    </a>
                </div>
            </div>

            {{-- Body --}}
            <div class="card-body px-3 pt-2 pb-0">
                <table class="table display table-row-rounded" id="tblBloodIssues">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Bag No</th>
                            <th>Blood Group</th>
                            <th>Donor</th>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Issue Date</th>
                            <th>Technician</th>
                            <th>Reference</th>
                            <th>Charge</th>
                            <th>Issued By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($issues as $issue)
                            <tr class="bi-row" data-index="{{ $loop->index }}">
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-bold">{{ $issue->bloodCollection->bag_no ?? '-' }}</td>
                                <td>{{ $issue->bloodCollection->bloodGroup->combined ?? '-' }}</td>
                                <td>{{ $issue->bloodCollection->donor->name ?? '-' }}</td>
                                <td>{{ $issue->patient->patient_name ?? '-' }}</td>
                                <td>{{ $issue->doctor->name ?? '-' }}</td>
                                <td>{{ $issue->issue_datetime->format('d M Y, h:i A') }}</td>
                                <td>{{ $issue->technician_name ?? '-' }}</td>
                                <td>{{ $issue->reference_name ?? '-' }}</td>
                                <td>{{ $issue->charge->charge_name ?? '-' }}</td>
                                <td>{{ $issue->createdBy->name ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">No blood issues found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Footer --}}
            @if($issues->count() > 0)
                <div class="card-footer d-flex flex-wrap align-items-center justify-content-between gap-2 bg-white border-top">
                    <div class="d-flex align-items-center gap-2">
                        <label for="biPerPage" class="mb-0 small text-muted">Rows per page:</label>
                        <select id="biPerPage" class="form-select form-select-sm" style="width:auto;">
                            <option value="5">5</option>
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                    <span id="biRangeInfo" class="small text-muted"></span>
                    <nav>
                        <ul id="biPagination" class="pagination pagination-sm mb-0"></ul>
                    </nav>
                </div>
            @endif

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const rows = Array.from(document.querySelectorAll('.bi-row'));
            if (!rows.length) return;

            const perPageSel = document.getElementById('biPerPage');
            const rangeInfo  = document.getElementById('biRangeInfo');
            const pagUl      = document.getElementById('biPagination');
            let currentPage  = 1;

            function render() {
                const perPage = parseInt(perPageSel.value);
                const total   = rows.length;
                const pages   = Math.ceil(total / perPage);
                if (currentPage > pages) currentPage = pages;

                const start = (currentPage - 1) * perPage;
                const end   = Math.min(start + perPage, total);

                rows.forEach((r, i) => r.style.display = (i >= start && i < end) ? '' : 'none');
                rangeInfo.textContent = `Showing ${start + 1}–${end} of ${total}`;

                // Build pagination
                let html = `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${currentPage - 1}">&laquo;</a></li>`;

                for (let p = 1; p <= pages; p++) {
                    if (p === 1 || p === pages || (p >= currentPage - 1 && p <= currentPage + 1)) {
                        html += `<li class="page-item ${p === currentPage ? 'active' : ''}"><a class="page-link" href="#" data-page="${p}">${p}</a></li>`;
                    } else if (p === currentPage - 2 || p === currentPage + 2) {
                        html += `<li class="page-item disabled"><span class="page-link">&hellip;</span></li>`;
                    }
                }

                html += `<li class="page-item ${currentPage === pages ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${currentPage + 1}">&raquo;</a></li>`;
                pagUl.innerHTML = html;
            }

            pagUl.addEventListener('click', function (e) {
                e.preventDefault();
                const btn = e.target.closest('[data-page]');
                if (!btn || btn.closest('.disabled')) return;
                currentPage = parseInt(btn.dataset.page);
                render();
            });

            perPageSel.addEventListener('change', function () {
                currentPage = 1;
                render();
            });

            render();
        })();
    </script>
@endpush
