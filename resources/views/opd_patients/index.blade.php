@extends('backend.layouts.master')

@section('title', 'OPD Patient List')

@section('content')
    <div class="container-fluid p-0">
        <div class="card border-0 shadow-sm rounded-0 opd-list-card">

            {{-- Header --}}
            <div class="card-header bg-white border-bottom p-0">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 px-3 pt-2">
                    <ul class="nav nav-tabs custom-tabs border-0 mb-0" id="opdTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="today-opd-tab" data-bs-toggle="tab"
                                data-bs-target="#today-opd-pane" type="button" role="tab"
                                aria-controls="today-opd-pane" aria-selected="true">
                                Today OPD
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="upcoming-opd-tab" data-bs-toggle="tab"
                                data-bs-target="#upcoming-opd-pane" type="button" role="tab"
                                aria-controls="upcoming-opd-pane" aria-selected="false">
                                Upcomming OPD
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="old-opd-tab" data-bs-toggle="tab" data-bs-target="#old-opd-pane"
                                type="button" role="tab" aria-controls="old-opd-pane" aria-selected="false">
                                Old OPD
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="patient-view-tab" data-bs-toggle="tab"
                                data-bs-target="#patient-view-pane" type="button" role="tab"
                                aria-controls="patient-view-pane" aria-selected="false">
                                Patient View
                            </button>
                        </li>
                    </ul>

                    <div class="pb-2">
                        <a href="{{ route('opd-patients.create') }}" class="btn btn-primary btn-sm add-btn">
                            <i class="bi bi-plus-lg me-1"></i>Add OPD Patient
                        </a>
                    </div>
                </div>
            </div>

            {{-- Tab Content --}}
            <div class="tab-content" id="opdTabsContent">

                {{-- TODAY OPD --}}
                <div class="tab-pane fade show active" id="today-opd-pane" role="tabpanel" aria-labelledby="today-opd-tab">
                    @include('components.opd.today-opd-tab')
                </div>

                {{-- UPCOMING OPD --}}
                <div class="tab-pane fade" id="upcoming-opd-pane" role="tabpanel" aria-labelledby="upcoming-opd-tab">
                    @include('components.opd.upcoming-opd-tab')
                </div>

                {{-- OLD OPD --}}
                <div class="tab-pane fade" id="old-opd-pane" role="tabpanel" aria-labelledby="old-opd-tab">
                    @include('components.opd.old-opd-tab')
                </div>

                {{-- PATIENT VIEW --}}
                <div class="tab-pane fade" id="patient-view-pane" role="tabpanel" aria-labelledby="patient-view-tab">
                    @include('components.opd.patient-view-tab')
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="ajaxCommonModal" tabindex="-1" aria-labelledby="ajaxCommonModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header text-white" style="background: linear-gradient(90deg, #1d84d7, #28a3f0);">
                    <h5 class="modal-title fw-bold" id="ajaxCommonModalLabel">Details</h5>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-light d-none" id="ajaxModalPrintBtn" title="Print">
                            <i class="bi bi-printer"></i>
                        </button>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body p-0" id="ajaxCommonModalBody">
                    <div class="p-4 text-center">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function printModalContent(title) {
            let printContent = document.getElementById('ajaxCommonModalBody').innerHTML;
            let printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head>
                    <title>${title}</title>
                    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
                    <style>
                        body { margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
                        .rx-doc { color: #333; background: #fff; }
                        .rx-header { padding: 25px 30px 0; }
                        .rx-hospital-name { font-size: 24px; font-weight: 700; color: #222; margin: 0; line-height: 1.2; }
                        .rx-hospital-logo { font-size: 13px; font-weight: 700; background: #e6e6e6; border: 1px solid #999; padding: 2px 8px; display: inline-block; margin-bottom: 4px; color: #333; }
                        .rx-contact { font-size: 12.5px; text-align: right; line-height: 1.6; color: #333; }
                        .rx-title-bar { background: #000; color: #fff; text-align: center; font-weight: 700; font-size: 14px; padding: 5px 0; margin: 12px 30px 0; }
                        .rx-body { padding: 15px 30px 20px; }
                        .rx-opd-row { font-size: 13px; color: #c47200; line-height: 1.6; }
                        .rx-info-row { display: flex; flex-wrap: wrap; padding: 6px 0; font-size: 13px; line-height: 1.8; border-bottom: 1px solid #e0e0e0; }
                        .rx-info-row:last-child { border-bottom: none; }
                        .rx-label { font-weight: 700; color: #1a6e3a; min-width: 140px; white-space: nowrap; }
                        .rx-label::after { content: ':'; margin-right: 8px; }
                        .rx-value { color: #333; }
                        .rx-info-cell { flex: 1; display: flex; min-width: 200px; }
                        .rx-writing-area { min-height: 450px; border: 1px solid #e0e0e0; margin-top: 15px; }
                        .rx-footer { font-size: 12px; color: #c47200; font-style: italic; padding: 12px 30px; }
                        @media print { body { padding: 0; } }
                    </style>
                </head>
                <body>${printContent}</body>
                </html>
            `);
            printWindow.document.close();
            printWindow.onload = function() {
                printWindow.print();
                printWindow.close();
            };
        }

        $(document).on('click', '.open-ajax-modal', function() {
            let url = $(this).data('url');
            let title = $(this).data('modal-title') || 'Details';
            let autoPrint = $(this).data('auto-print') || false;

            $('#ajaxCommonModalLabel').text(title);
            $('#ajaxModalPrintBtn').toggleClass('d-none', !autoPrint);
            $('#ajaxCommonModalBody').html(`
            <div class="p-4 text-center">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
        `);

            let modal = new bootstrap.Modal(document.getElementById('ajaxCommonModal'));
            modal.show();

            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    $('#ajaxCommonModalBody').html(response);

                    if (autoPrint) {
                        setTimeout(function() {
                            printModalContent(title);
                        }, 300);
                    }
                },
                error: function() {
                    $('#ajaxCommonModalBody').html(`
                    <div class="p-4 text-danger text-center">
                        Unable to load data.
                    </div>
                `);
                }
            });
        });

        $(document).on('click', '#ajaxModalPrintBtn', function() {
            printModalContent($('#ajaxCommonModalLabel').text());
        });

    </script>
@endpush

@push('styles')
    <style>
        .opd-list-card {
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

        .page-size-select {
            width: 70px;
            border: 0;
            border-bottom: 1px solid #d7dce2;
            border-radius: 0;
            font-size: 12px;
            box-shadow: none !important;
            padding-left: 4px;
            padding-right: 22px;
        }

        .icon-btn {
            color: #2f3b4a;
            font-size: 15px;
            text-decoration: none;
            line-height: 1;
        }

        .icon-btn:hover {
            color: #0d6efd;
        }

        .opd-table-wrap {
            min-height: 430px;
        }

        .opd-table {
            font-size: 11.5px;
            color: #2f3b4a;
        }

        .opd-table thead th {
            font-size: 11.5px;
            font-weight: 700;
            color: #1f2937;
            border-bottom: 1px solid #dfe5eb;
            padding: 8px 10px;
            white-space: nowrap;
            vertical-align: middle;
            background: #fff;
        }

        .opd-table tbody td {
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
            top: 28px;
        }

        .paper-1 {
            top: 10px;
            left: 18px;
            transform: rotate(-2deg);
        }

        .paper-2 {
            top: 0;
            left: 58px;
            transform: rotate(0deg);
        }

        .paper-3 {
            top: 18px;
            left: 35px;
            width: 80px;
            height: 105px;
            z-index: 2;
        }

        .folder-front {
            position: absolute;
            left: 10px;
            right: 10px;
            bottom: 0;
            height: 82px;
            background: #d9dde2;
            border: 2px solid #3b3f45;
            border-top: 1px solid #3b3f45;
            border-radius: 3px 3px 8px 8px;
            transform: skewX(-4deg);
            z-index: 3;
        }

        .empty-message {
            font-size: 12px;
            font-weight: 700;
            color: #2d6b2f;
        }

        .table-footer {
            font-size: 11.5px;
            color: #1f2937;
        }

        .record-info {
            font-size: 11.5px;
        }

        .page-nav {
            color: #697586;
            font-size: 14px;
            text-decoration: none;
        }

        .page-nav:hover {
            color: #0d6efd;
        }

        @media (max-width: 991.98px) {
            .search-box-wrap {
                width: 220px;
            }

            .toolbar-actions {
                width: 100%;
                justify-content: space-between !important;
            }

            .opd-table {
                min-width: 1000px;
            }
        }

        @media (max-width: 767.98px) {
            .card-body {
                padding: 10px !important;
            }

            .custom-tabs .nav-link {
                font-size: 11px;
                padding: 8px 7px 10px;
            }

            .add-btn {
                font-size: 11px;
                padding: 6px 10px;
            }

            .search-box-wrap {
                width: 100%;
            }

            .toolbar-actions {
                gap: 10px !important;
            }

            .table-icons {
                gap: 10px !important;
            }

            .opd-table {
                min-width: 1000px;
                font-size: 11px;
            }

            .opd-table thead th,
            .opd-table tbody td,
            .record-info,
            .table-footer {
                font-size: 11px;
            }

            .empty-state-wrapper {
                min-height: 320px;
                padding: 20px 10px;
            }

            .empty-illustration {
                height: 180px;
            }
        }
    </style>
@endpush
