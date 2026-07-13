@extends('backend.layouts.master')
@section('title', 'Show Ipd Patient')

@section('content')
    <div class="container-fluid py-3">

        {{-- Header --}}
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body py-3">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                    <div>
                        <h4 class="mb-1 fw-bold">Ipd Patient Profile</h4>
                        <p class="text-muted mb-0 small">Patient details, billing, medication, nurse notes, investigations
                            and more
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabs Section --}}
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white pb-0 border-0">
                <div class="d-flex align-items-center border-bottom">
                    <button type="button" class="btn btn-light btn-sm rounded-1 m-2 flex-shrink-1" style="border:1; background-color: #70a2ed;" onclick="document.getElementById('ipdTabsWrapper').scrollBy({left:-200,behavior:'smooth'})">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <div class="overflow-auto flex-grow-1" id="ipdTabsWrapper">
                    <ul class="nav nav-tabs flex-nowrap border-bottom-0" id="ipdTabs" role="tablist" style="white-space: nowrap;">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active d-flex align-items-center gap-2" id="overview-tab"
                                data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">
                                <i class="bi bi-grid"></i> <span>Overview</span>
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button class="nav-link d-flex align-items-center gap-2" id="vital-check-tab"
                                data-bs-toggle="tab" data-bs-target="#vital-check" type="button" role="tab">
                                <i class="bi bi-heart-pulse"></i> <span>Vital Check</span>
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button class="nav-link d-flex align-items-center gap-2" id="nurse-tab" data-bs-toggle="tab"
                                data-bs-target="#nurse" type="button" role="tab">
                                <i class="bi bi-journal-medical"></i> <span>Case Nurse</span>
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button class="nav-link d-flex align-items-center gap-2" id="rounddr-tab" data-bs-toggle="tab"
                                data-bs-target="#rounddr" type="button" role="tab">
                                <i class="bi bi-journal-medical"></i> <span>Round Dr</span>
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button class="nav-link d-flex align-items-center gap-2" id="casedr-tab" data-bs-toggle="tab"
                                data-bs-target="#casedr" type="button" role="tab">
                                <i class="bi bi-journal-medical"></i> <span>Case Dr</span>
                            </button>
                        </li>

                        {{-- <li class="nav-item" role="presentation">
                            <button class="nav-link d-flex align-items-center gap-2" id="operation-tab" data-bs-toggle="tab"
                                data-bs-target="#operation" type="button" role="tab">
                                <i class="bi bi-journal-medical"></i> <span>Case Operation</span>
                            </button>
                        </li> --}}

                        <li class="nav-item" role="presentation">
                            <button class="nav-link d-flex align-items-center gap-2" id="surgery-request-tab"
                                data-bs-toggle="tab" data-bs-target="#surgery-request" type="button" role="tab">
                                <i class="bi bi-scissors"></i> <span>Surgery Request</span>
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button class="nav-link d-flex align-items-center gap-2" id="bed-history-tab"
                                data-bs-toggle="tab" data-bs-target="#bed-history" type="button" role="tab">
                                <i class="bi bi-hospital"></i> <span>Bed History</span>
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button class="nav-link d-flex align-items-center gap-2" id="medicine-order-tab"
                                data-bs-toggle="tab" data-bs-target="#medicine-order" type="button" role="tab">
                                <i class="bi bi-prescription2"></i> <span>Medicine Order</span>
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button class="nav-link d-flex align-items-center gap-2" id="medication-tab"
                                data-bs-toggle="tab" data-bs-target="#medication" type="button" role="tab">
                                <i class="bi bi-eyedropper"></i> <span>Medication</span>
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button class="nav-link d-flex align-items-center gap-2" id="prescription-tab"
                                data-bs-toggle="tab" data-bs-target="#prescription" type="button" role="tab">
                                <i class="bi bi-capsule"></i> <span>Prescription</span>
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button class="nav-link d-flex align-items-center gap-2" id="lab-tab" data-bs-toggle="tab"
                                data-bs-target="#lab" type="button" role="tab">
                                <i class="bi bi-clipboard2-pulse"></i> <span>Lab Orders</span>
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button class="nav-link d-flex align-items-center gap-2" id="treatment-history-tab" data-bs-toggle="tab"
                                data-bs-target="#treatment-history" type="button" role="tab">
                                <i class="bi bi-clipboard2-pulse"></i> <span>Treatment History</span>
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button class="nav-link d-flex align-items-center gap-2" id="timeline-tab" data-bs-toggle="tab"
                                data-bs-target="#timeline" type="button" role="tab">
                                <i class="bi bi-clock-history"></i> <span>Timeline</span>
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button class="nav-link d-flex align-items-center gap-2" id="charges-tab" data-bs-toggle="tab"
                                data-bs-target="#charges" type="button" role="tab">
                                <i class="bi bi-cash-stack"></i> <span>Charges</span>
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button class="nav-link d-flex align-items-center gap-2" id="payments-tab" data-bs-toggle="tab"
                                data-bs-target="#payments" type="button" role="tab">
                                <i class="bi bi-credit-card-2-front"></i> <span>Payments</span>
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button class="nav-link d-flex align-items-center gap-2" id="package-tab" data-bs-toggle="tab"
                                data-bs-target="#package" type="button" role="tab">
                                <i class="bi bi-box-seam"></i> <span>Package</span>
                            </button>
                        </li>
                    </ul>
                    </div>
                    <button type="button" class="btn btn-light btn-sm rounded-1 m-2 flex-shrink-1" style="border:1; background-color: #70a2ed;" onclick="document.getElementById('ipdTabsWrapper').scrollBy({left:200,behavior:'smooth'})">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>

            <div class="card-body">
                <div class="tab-content" id="ipdTabContent">

                    {{-- Overview --}}
                    <div class="tab-pane fade show active" id="overview" role="tabpanel">
                        <x-ipd.overview :iPDPatient="$iPDPatient" />
                    </div>

                    {{-- Nurse Notes --}}
                    <div class="tab-pane fade" id="nurse" role="tabpanel">
                        <x-ipd.nurse-notes :iPDPatient="$iPDPatient" />
                    </div>

                    {{-- Round Dr --}}
                    <div class="tab-pane fade" id="rounddr" role="tabpanel">
                        <x-ipd.round-drs :iPDPatient="$iPDPatient" />
                    </div>

                    {{-- Case Dr --}}
                    <div class="tab-pane fade" id="casedr" role="tabpanel">
                        <x-ipd.case-drs :iPDPatient="$iPDPatient" />
                    </div>

                    {{-- Operation Notes --}}
                    {{-- <div class="tab-pane fade" id="operation" role="tabpanel">
                        <x-ipd.case-operations :iPDPatient="$iPDPatient" />
                    </div> --}}

                    {{-- Surgery Request --}}
                    <div class="tab-pane fade" id="surgery-request" role="tabpanel">
                        <x-ipd.surgery-requests :iPDPatient="$iPDPatient" />
                    </div>

                    {{-- Bed History --}}
                    <div class="tab-pane fade" id="bed-history" role="tabpanel">
                        <x-ipd.bed-history :bedAllocations="$iPDPatient->bedAllocations" :iPDPatient="$iPDPatient" />
                    </div>

                    {{-- Medicine Order --}}
                    <div class="tab-pane fade" id="medicine-order" role="tabpanel">
                        <x-ipd.medicine-orders :iPDPatient="$iPDPatient" />
                    </div>

                    {{-- Medication --}}
                    <div class="tab-pane fade" id="medication" role="tabpanel">
                        <x-ipd.medications :iPDPatient="$iPDPatient" />
                    </div>


                    {{-- Prescription --}}
                    <div class="tab-pane fade" id="prescription" role="tabpanel">
                        <x-ipd.prescriptions :iPDPatient="$iPDPatient" />
                    </div>

                    {{-- Vital Check --}}
                    <div class="tab-pane fade" id="vital-check" role="tabpanel">
                        <x-ipd.vital-checks :iPDPatient="$iPDPatient" />
                    </div>

                    {{-- Lab Orders — Pathology + Radiology in one tab, grouped by type --}}
                    <div class="tab-pane fade" id="lab" role="tabpanel">
                        <x-ipd.lab-orders :iPDPatient="$iPDPatient" />
                    </div>

                    {{-- Treatment History --}}
                    <div class="tab-pane fade" id="treatment-history" role="tabpanel">
                        <x-ipd.treatment-histories :iPDPatient="$iPDPatient" />
                    </div>

                    {{-- Timeline --}}
                    <div class="tab-pane fade" id="timeline" role="tabpanel">
                        <x-ipd.timeline :iPDPatient="$iPDPatient" />
                    </div>

                    {{-- Charges --}}
                    <div class="tab-pane fade" id="charges" role="tabpanel">
                        <x-ipd.charges :iPDPatient="$iPDPatient" />
                    </div>

                    {{-- Payments --}}
                    <div class="tab-pane fade" id="payments" role="tabpanel">
                        <x-ipd.payments :iPDPatient="$iPDPatient" />
                    </div>

                    {{-- Package Enrollments --}}
                    <div class="tab-pane fade" id="package" role="tabpanel">
                        <x-ipd.package :iPDPatient="$iPDPatient" />
                    </div>

                </div>
            </div>
        </div>




    </div>

    <style>
        .nav-tabs .nav-link.active {
            background: #0d6efd;
            color: #fff;
            border-radius: 6px 6px 0 0;
        }
    </style>

    @push('scripts')
        <script>
            $(function () {
                var params = new URLSearchParams(window.location.search);
                var tab = params.get('tab');
                if (tab) {
                    var trigger = document.getElementById(tab + '-tab');
                    if (trigger) bootstrap.Tab.getOrCreateInstance(trigger).show();
                }

                document.querySelectorAll('#ipdTabs button[data-bs-toggle="tab"]').forEach(function (btn) {
                    btn.addEventListener('shown.bs.tab', function (e) {
                        var id = e.target.id.replace(/-tab$/, '');
                        var url = new URL(window.location.href);
                        url.searchParams.set('tab', id);
                        window.history.replaceState(null, '', url);
                    });
                });
            });
        </script>
    @endpush

    @if (session('print_slips'))
        @php $slips = session('print_slips'); @endphp
        <script>
            (function () {
                var slips = @json($slips);
                Object.values(slips).forEach(function (url, i) {
                    setTimeout(function () { window.open(url, '_blank'); }, 250 + (i * 400));
                });
            })();
        </script>
    @endif
@endsection
