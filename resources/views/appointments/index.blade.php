@extends('backend.layouts.master')

@section('title', 'Appointments')

@section('content')
    @php($activeTab = request('tab', 'today'))

    <div class="container-fluid p-0">
        <div class="card border-0 shadow-sm rounded-0 appointment-list-card">

            {{-- Header --}}
            <div class="card-header bg-white border-bottom p-0">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 px-3 pt-2">
                    <ul class="nav nav-tabs custom-tabs border-0 mb-0" id="appointmentTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $activeTab === 'today' ? 'active' : '' }}" id="today-apt-tab" data-bs-toggle="tab"
                                data-bs-target="#today-apt-pane" type="button" role="tab"
                                aria-controls="today-apt-pane" aria-selected="{{ $activeTab === 'today' ? 'true' : 'false' }}">
                                Today Appointment
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $activeTab === 'upcoming' ? 'active' : '' }}" id="upcoming-apt-tab" data-bs-toggle="tab"
                                data-bs-target="#upcoming-apt-pane" type="button" role="tab"
                                aria-controls="upcoming-apt-pane" aria-selected="{{ $activeTab === 'upcoming' ? 'true' : 'false' }}">
                                Upcoming Appointment
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $activeTab === 'old' ? 'active' : '' }}" id="old-apt-tab" data-bs-toggle="tab"
                                data-bs-target="#old-apt-pane" type="button" role="tab"
                                aria-controls="old-apt-pane" aria-selected="{{ $activeTab === 'old' ? 'true' : 'false' }}">
                                Old Appointment
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $activeTab === 'patient' ? 'active' : '' }}" id="patient-view-tab" data-bs-toggle="tab"
                                data-bs-target="#patient-view-pane" type="button" role="tab"
                                aria-controls="patient-view-pane" aria-selected="{{ $activeTab === 'patient' ? 'true' : 'false' }}">
                                Patient View
                            </button>
                        </li>
                    </ul>

                    <div class="pb-2 d-flex gap-2">
                        <a href="{{ route('appointments.doctor-wise') }}" class="btn btn-outline-primary btn-sm add-btn">
                            <i class="bi bi-person-vcard me-1"></i>Doctor Wise
                        </a>
                        <a href="{{ route('patient-queue.index') }}" class="btn btn-outline-primary btn-sm add-btn">
                            <i class="bi bi-people me-1"></i>Patient Queue
                        </a>
                        <a href="{{ route('appointments.create') }}" class="btn btn-primary btn-sm add-btn">
                            <i class="bi bi-plus-lg me-1"></i>Add Appointment
                        </a>
                    </div>
                </div>
            </div>

            {{-- Success Message --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show m-3 mb-0" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Tab Content --}}
            <div class="tab-content" id="appointmentTabsContent">

                {{-- TODAY APPOINTMENT --}}
                <div class="tab-pane fade {{ $activeTab === 'today' ? 'show active' : '' }}" id="today-apt-pane" role="tabpanel"
                    aria-labelledby="today-apt-tab">
                    @include('components.appointment.today-appointment-tab')
                </div>

                {{-- UPCOMING APPOINTMENT --}}
                <div class="tab-pane fade {{ $activeTab === 'upcoming' ? 'show active' : '' }}" id="upcoming-apt-pane" role="tabpanel"
                    aria-labelledby="upcoming-apt-tab">
                    @include('components.appointment.upcoming-appointment-tab')
                </div>

                {{-- OLD APPOINTMENT --}}
                <div class="tab-pane fade {{ $activeTab === 'old' ? 'show active' : '' }}" id="old-apt-pane" role="tabpanel"
                    aria-labelledby="old-apt-tab">
                    @include('components.appointment.old-appointment-tab')
                </div>

                {{-- PATIENT VIEW --}}
                <div class="tab-pane fade {{ $activeTab === 'patient' ? 'show active' : '' }}" id="patient-view-pane" role="tabpanel"
                    aria-labelledby="patient-view-tab">
                    @include('components.appointment.patient-view-tab')
                </div>

            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .appointment-list-card {
            background: #fff;
            border: 1px solid #d8dee6;
        }

        .custom-tabs {
            gap: 6px;
            flex-wrap: nowrap;
            overflow-x: auto;
            white-space: nowrap;
            scrollbar-width: thin;
        }

        .custom-tabs .nav-link {
            border: 0 !important;
            border-bottom: 2px solid transparent !important;
            border-radius: 0 !important;
            background: transparent !important;
            color: #2f3b4a;
            font-size: 12px;
            font-weight: 500;
            padding: 10px 8px 12px;
            margin-bottom: 0;
        }

        .custom-tabs .nav-link.active {
            color: #0d6efd;
            border-bottom-color: #0d6efd !important;
        }

        .add-btn {
            font-size: 12px;
            font-weight: 600;
            padding: 7px 12px;
            border-radius: 3px;
        }

        .search-box-wrap {
            width: 280px;
            max-width: 100%;
        }

        .search-input {
            border: 0;
            border-bottom: 1px solid #d7dce2;
            border-radius: 0;
            padding-left: 0;
            padding-right: 0;
            font-size: 11.5px;
            color: #374151;
            box-shadow: none !important;
        }

        .search-input:focus {
            border-bottom-color: #0d6efd;
        }

        .apt-table-wrap {
            min-height: 430px;
        }

        .apt-table {
            font-size: 11.5px;
            color: #2f3b4a;
        }

        .apt-table thead th {
            font-size: 11.5px;
            font-weight: 700;
            color: #1f2937;
            border-bottom: 1px solid #dfe5eb;
            padding: 8px 10px;
            white-space: nowrap;
            vertical-align: middle;
            background: #fff;
        }

        .apt-table tbody td {
            font-size: 11.5px;
            padding: 8px 10px;
            border-bottom: 1px solid #eef2f6;
            color: #374151;
            vertical-align: middle;
        }

        .action-btn {
            padding: 3px 7px;
            font-size: 11px;
            border-radius: 3px;
        }

        .status-badge {
            font-size: 10.5px;
            font-weight: 600;
            padding: 4px 7px;
            border-radius: 3px;
        }

        .empty-state-wrapper {
            min-height: 400px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 14px;
            text-align: center;
            padding: 30px 20px;
            border-bottom: 1px solid #eef2f6;
        }

        .empty-title {
            font-size: 12px;
            color: #f0a5a5;
            font-weight: 500;
        }

        .empty-illustration {
            height: 230px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .folder-box {
            position: relative;
            width: 150px;
            height: 120px;
        }

        .paper {
            position: absolute;
            width: 70px;
            height: 95px;
            background: #fff;
            border: 1px solid #aeb7c2;
            border-radius: 2px;
        }

        .paper::before,
        .paper::after {
            content: "";
            position: absolute;
            left: 10px;
            right: 10px;
            height: 2px;
            background: #9ec5fe;
        }

        .paper::before {
            top: 18px;
        }

        .paper::after {
            top: 30px;
            right: 24px;
        }

        .paper-1 {
            left: 25px;
            top: 0;
            transform: rotate(-5deg);
            z-index: 1;
        }

        .paper-2 {
            left: 35px;
            top: -3px;
            transform: rotate(2deg);
            z-index: 2;
        }

        .paper-3 {
            left: 40px;
            top: -6px;
            z-index: 3;
        }

        .folder-front {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 75px;
            background: #f8d775;
            border: 1px solid #c7a83b;
            border-radius: 0 0 6px 6px;
            z-index: 4;
        }

        .folder-front::before {
            content: "";
            position: absolute;
            top: -18px;
            left: 0;
            width: 55px;
            height: 18px;
            background: #f8d775;
            border: 1px solid #c7a83b;
            border-bottom: 0;
            border-radius: 6px 6px 0 0;
        }

        .empty-message {
            font-size: 11px;
            color: #6b7280;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(function() {
            $('.search-input').on('keyup', function() {
                var value = $(this).val().toLowerCase();
                var $table = $(this).closest('.card-body').find('.apt-table tbody tr');

                $table.each(function() {
                    var rowText = $(this).text().toLowerCase();
                    $(this).toggle(rowText.indexOf(value) > -1);
                });

                // Update record count
                var visible = $(this).closest('.card-body').find('.apt-table tbody tr:visible').length;
                var total = $table.length;
                var $info = $(this).closest('.card-body').find('.record-info');

                if (value) {
                    $info.text('Showing ' + visible + ' of ' + total + ' records');
                } else {
                    $info.text('Records: ' + total);
                }
            });
        });
    </script>
@endpush
