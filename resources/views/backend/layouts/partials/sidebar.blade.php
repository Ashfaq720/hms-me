<!-- begin::GXON Sidebar Menu -->
<aside class="app-menubar" id="appMenubar">
    <div class="app-navbar-brand">
        {{-- <a class="navbar-brand-logo" href="{{ route('dashboard') }}">
            <img src="{{ asset('backend/assets/images/logo.svg') }}" alt="GXON Admin Dashboard Logo">
        </a>
        <a class="navbar-brand-mini visible-light" href="{{ route('dashboard') }}">
            <img src="{{ asset('backend/assets/images/logo-text.svg') }}" alt="GXON Admin Dashboard Logo">
        </a>
        <a class="navbar-brand-mini visible-dark" href="{{ route('dashboard') }}">
            <img src="{{ asset('backend/assets/images/logo-text-white.svg') }}" alt="GXON Admin Dashboard Logo">
        </a> --}}

        <a class="navbar-brand-mini visible-light" href="{{ route('dashboard') }}">
            <img src="{{ asset(setting('company_logo')) }}" alt="{{ setting('company_name') }} Logo">
        </a>
    </div>

    <nav class="app-navbar" data-simplebar>
        <ul class="menubar">

            {{-- ───────── OVERVIEW ───────── --}}
            <li class="menu-heading px-3 py-2 mt-2 text-uppercase fw-semibold text-muted small">Overview</li>

            <li class="menu-item">
                <a class="menu-link{{ request()->routeIs('dashboard') ? ' active' : '' }}"
                    href="{{ route('dashboard') }}">
                    <i class="fi fi-rr-dashboard"></i>
                    <span class="menu-label">Dashboard</span>
                </a>
            </li>

            {{-- ───────── COMMAND CENTERS ───────── --}}
            <li class="menu-item menu-arrow{{ request()->routeIs('admin.centers.*') ? ' route-active' : '' }}">
                <a class="menu-link{{ request()->routeIs('admin.centers.*') ? ' active' : '' }}"
                    href="javascript:void(0);" role="button">
                    <i class="fi fi-rr-objects-column"></i>
                    <span class="menu-label">Command Centers</span>
                </a>
                <ul class="menu-inner">
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('admin.centers.clinical') ? ' active' : '' }}"
                            href="{{ route('admin.centers.clinical') }}">
                            <i class="bi bi-heart-pulse text-danger"></i>
                            <span class="menu-label">Clinical Center</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('admin.centers.billing') ? ' active' : '' }}"
                            href="{{ route('admin.centers.billing') }}">
                            <i class="bi bi-cash-stack text-success"></i>
                            <span class="menu-label">Billing Center</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('admin.centers.inventory') ? ' active' : '' }}"
                            href="{{ route('admin.centers.inventory') }}">
                            <i class="bi bi-boxes text-warning"></i>
                            <span class="menu-label">Inventory Hub</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('admin.centers.equipment') ? ' active' : '' }}"
                            href="{{ route('admin.centers.equipment') }}">
                            <i class="bi bi-plug text-info"></i>
                            <span class="menu-label">Equipment Center</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('admin.centers.master-data') ? ' active' : '' }}"
                            href="{{ route('admin.centers.master-data') }}">
                            <i class="bi bi-database text-primary"></i>
                            <span class="menu-label">Master Data Center</span>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('admin.hub.reports') ? ' active' : '' }}"
                            href="{{ route('admin.hub.reports') }}">
                            <i class="bi bi-file-earmark-bar-graph text-primary"></i>
                            <span class="menu-label">Reports Hub</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('admin.hub.audit') ? ' active' : '' }}"
                            href="{{ route('admin.hub.audit') }}">
                            <i class="bi bi-eye text-warning"></i>
                            <span class="menu-label">Audit Log</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('admin.hub.wallet') ? ' active' : '' }}"
                            href="{{ route('admin.hub.wallet') }}">
                            <i class="bi bi-wallet2 text-success"></i>
                            <span class="menu-label">Doctor Wallet</span>
                        </a>
                    </li>
                </ul>
            </li>

            {{-- ───────── PATIENT FLOW ───────── --}}
            <li class="menu-heading px-3 py-2 mt-3 text-uppercase fw-semibold text-muted small">Patient Flow</li>

            {{-- Doctor Portal — only shows for users that ARE doctors --}}
            @if (auth()->user() && auth()->user()->doctor()->exists())
            <li class="menu-item">
                <a class="menu-link{{ request()->routeIs('doctor-portal.*') ? ' active' : '' }}"
                    href="{{ route('doctor-portal.index') }}">
                    <i class="bi bi-person-vcard text-primary"></i>
                    <span class="menu-label">My Patients</span>
                </a>
            </li>
            @endif

            <li class="menu-item">
                <a class="menu-link{{ request()->routeIs('front_desk.index') ? ' active' : '' }}"
                    href="{{ route('front_desk.index') }}">
                    <i class="fa fa-user-tie"></i>
                    <span class="menu-label">Front Desk</span>
                </a>
            </li>

            <li class="menu-item">
                <a class="menu-link{{ request()->routeIs('front_desk.live-vitals.*') ? ' active' : '' }}"
                    href="{{ route('front_desk.live-vitals.index') }}">
                    <i class="bi bi-heart-pulse-fill text-danger"></i>
                    <span class="menu-label">Live Vitals</span>
                </a>
            </li>

            <li class="menu-item">
                <a class="menu-link{{ request()->routeIs('opd-patients.*') ? ' active' : '' }}"
                    href="{{ route('opd-patients.index') }}">
                    <i class="fi fi-rr-stethoscope"></i>
                    <span class="menu-label">OPD Patient</span>
                </a>
            </li>

            <li class="menu-item">
                <a class="menu-link{{ request()->routeIs('ipd-patients.*') ? ' active' : '' }}"
                    href="{{ route('ipd-patients.index') }}">
                    <i class="fi fi-rr-hospital"></i>
                    <span class="menu-label">IPD Patient</span>
                </a>
            </li>

            {{-- ───────── CRITICAL CARE ───────── --}}
            <li class="menu-heading px-3 py-2 mt-3 text-uppercase fw-semibold text-muted small">Critical Care</li>

            @php
                $sidebarIcuType = request('icu_type');

                // On admission sub-routes the URL has no icu_type query param,
                // so derive it from the admission record itself.
                if (!$sidebarIcuType && request()->routeIs('icu.admissions.*')) {
                    $admissionId = request()->route('admissionId') ?? request()->route('id');
                    if ($admissionId) {
                        $sidebarIcuType = \App\Models\Icu\IcuAdmission::where('id', $admissionId)->value('icu_type');
                    }
                }

                $isCcuView = $sidebarIcuType === 'CCU';
                $icuActive = request()->routeIs('icu.*') && !$isCcuView;
                $ccuActive = request()->routeIs('icu.*') && $isCcuView;
            @endphp

            {{-- ICU --}}
            <li class="menu-item menu-arrow{{ $icuActive ? ' route-active' : '' }}">
                <a class="menu-link{{ $icuActive ? ' active' : '' }}" href="javascript:void(0);" role="button">
                    <i class="bi bi-heart-pulse"></i>
                    <span class="menu-label">ICU</span>
                </a>

                <ul class="menu-inner">
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('icu.dashboard') && !$isCcuView ? ' active' : '' }}"
                            href="{{ route('icu.dashboard', ['icu_type' => 'ICU']) }}">
                            <i class="bi bi-speedometer2"></i>
                            <span class="menu-label">Live Dashboard</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('icu.admissions.*') && !$isCcuView ? ' active' : '' }}"
                            href="{{ route('icu.admissions.index', ['icu_type' => 'ICU']) }}">
                            <i class="bi bi-clipboard2-pulse"></i>
                            <span class="menu-label">Admissions</span>
                        </a>
                    </li>
                    {{-- ICU Equipment + ICU Packages moved to unified menus
                         (Equipment Center · Package Management).
                         The legacy /icu/packages?icu_type=ICU URL still works for back-compat.
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('icu.packages.*') && !$isCcuView ? ' active' : '' }}"
                            href="{{ route('icu.packages.index', ['icu_type' => 'ICU']) }}">
                            <i class="bi bi-box-seam"></i>
                            <span class="menu-label">ICU Packages</span>
                        </a>
                    </li>
                    --}}
                    @php
                        $icuReportsActive = (request()->routeIs('icu.infection.reports') || request()->routeIs('icu.mortality.*') || request()->routeIs('icu.admissions.mortality.*')) && !$isCcuView;
                    @endphp
                    <li class="menu-item menu-arrow{{ $icuReportsActive ? ' route-active' : '' }}">
                        <a class="menu-link{{ $icuReportsActive ? ' active' : '' }}"
                            href="javascript:void(0);" role="button">
                            <i class="bi bi-file-earmark-bar-graph"></i>
                            <span class="menu-label">Reports</span>
                        </a>
                        <ul class="menu-inner">
                            <li class="menu-item">
                                <a class="menu-link{{ request()->routeIs('icu.infection.reports') && !$isCcuView ? ' active' : '' }}"
                                    href="{{ route('icu.infection.reports', ['icu_type' => 'ICU']) }}">
                                    <i class="bi bi-shield-exclamation"></i>
                                    <span class="menu-label">Infection</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link" href="javascript:void(0);">
                                    <i class="bi bi-box-arrow-right"></i>
                                    <span class="menu-label">Discharge</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link{{ (request()->routeIs('icu.mortality.*') || request()->routeIs('icu.admissions.mortality.*')) && !$isCcuView ? ' active' : '' }}"
                                    href="{{ route('icu.mortality.index', ['icu_type' => 'ICU']) }}">
                                    <i class="bi bi-heartbreak"></i>
                                    <span class="menu-label">Mortality</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <div class="menu-heading d-flex align-items-center gap-2 px-3 py-2 mt-2 text-uppercase fw-semibold text-primary"
                        style="font-size: 10px; letter-spacing: .5px; border-bottom: 1px solid var(--bs-border-color);">
                        <i class="fi fi-rs-interrogation"></i>
                        <span class="menu-label">Clinical Management</span>
                    </div>

                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('icu.orders.manage') && !$isCcuView ? ' active' : '' }}"
                            href="{{ route('icu.orders.manage', ['icu_type' => 'ICU']) }}">
                            <i class="bi bi-clipboard-check"></i>
                            <span class="menu-label">Order Management</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('icu.nursing-notes.manage') && !$isCcuView ? ' active' : '' }}"
                            href="{{ route('icu.nursing-notes.manage', ['icu_type' => 'ICU']) }}">
                            <i class="bi bi-journal-text"></i>
                            <span class="menu-label">Nursing Notes</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('icu.intake-output.manage') && !$isCcuView ? ' active' : '' }}"
                            href="{{ route('icu.intake-output.manage', ['icu_type' => 'ICU']) }}">
                            <i class="bi bi-droplet-half"></i>
                            <span class="menu-label">Intake/Output</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('icu.code-blue.*') && !$isCcuView ? ' active' : '' }}"
                            href="{{ route('icu.code-blue.index', ['icu_type' => 'ICU']) }}">
                            <i class="bi bi-exclamation-octagon-fill text-danger"></i>
                            <span class="menu-label">Code Blue</span>
                        </a>
                    </li>
                </ul>

            </li>

            {{-- OT Management --}}
            @canany([
                'ot_dashboard_access','ot_surgery_request_access','ot_schedule_access','ot_pre_op_access',
                'ot_transfer_access','ot_room_access','ot_team_access','ot_anesthesia_access',
                'ot_intra_op_access','ot_consumables_access','ot_post_op_access','ot_pacu_access',
                'ot_billing_access','ot_inventory_access','ot_cleaning_access','ot_documents_access',
                'ot_emergency_access','ot_reports_access','ot_setup_access'
            ])
            @php $otActive = request()->routeIs('ot.*'); @endphp
            <li class="menu-item menu-arrow{{ $otActive ? ' route-active' : '' }}">
                <a class="menu-link{{ $otActive ? ' active' : '' }}" href="javascript:void(0);" role="button">
                    <i class="bi bi-scissors"></i>
                    <span class="menu-label">OT Management</span>
                </a>
                <ul class="menu-inner">
                    @can('ot_dashboard_access')
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('ot.dashboard*') ? ' active' : '' }}"
                            href="{{ route('ot.dashboard') }}">
                            <i class="bi bi-speedometer2"></i>
                            <span class="menu-label">OT Dashboard</span>
                        </a>
                    </li>
                    @endcan
                    @can('ot_surgery_request_access')
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('ot.surgery-requests.*') ? ' active' : '' }}"
                            href="{{ route('ot.surgery-requests.index') }}">
                            <i class="bi bi-clipboard-plus"></i>
                            <span class="menu-label">Surgery Request</span>
                        </a>
                    </li>
                    @endcan
                    @can('ot_schedule_access')
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('ot.schedules.*') ? ' active' : '' }}"
                            href="{{ route('ot.schedules.index') }}">
                            <i class="bi bi-calendar-week"></i>
                            <span class="menu-label">Scheduling</span>
                        </a>
                    </li>
                    @endcan
                    @can('ot_pre_op_access')
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('ot.pre-op.*') ? ' active' : '' }}"
                            href="{{ route('ot.pre-op.index') }}">
                            <i class="bi bi-check2-square"></i>
                            <span class="menu-label">Pre-Operative</span>
                        </a>
                    </li>
                    @endcan
                    @can('ot_transfer_access')
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('ot.transfers.*') ? ' active' : '' }}"
                            href="{{ route('ot.transfers.index') }}">
                            <i class="bi bi-arrow-left-right"></i>
                            <span class="menu-label">Transfers</span>
                        </a>
                    </li>
                    @endcan
                    {{-- Rooms + Team moved into the OT Master submenu below to avoid duplication --}}
                    @can('ot_anesthesia_access')
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('ot.anesthesia.*') ? ' active' : '' }}"
                            href="{{ route('ot.anesthesia.index') }}">
                            <i class="bi bi-droplet"></i>
                            <span class="menu-label">Anesthesia</span>
                        </a>
                    </li>
                    @endcan
                    @can('ot_intra_op_access')
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('ot.intra-op.*') ? ' active' : '' }}"
                            href="{{ route('ot.intra-op.index') }}">
                            <i class="bi bi-activity"></i>
                            <span class="menu-label">Intra-Op</span>
                        </a>
                    </li>
                    @endcan
                    @can('ot_consumables_access')
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('ot.consumables.*') ? ' active' : '' }}"
                            href="{{ route('ot.consumables.index') }}">
                            <i class="bi bi-box-seam"></i>
                            <span class="menu-label">Consumable Logs</span>
                        </a>
                    </li>
                    @endcan
                    @can('ot_post_op_access')
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('ot.post-op.*') ? ' active' : '' }}"
                            href="{{ route('ot.post-op.index') }}">
                            <i class="bi bi-journal-medical"></i>
                            <span class="menu-label">Post-Op</span>
                        </a>
                    </li>
                    @endcan
                    @can('ot_pacu_access')
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('ot.pacu.*') ? ' active' : '' }}"
                            href="{{ route('ot.pacu.index') }}">
                            <i class="bi bi-bandaid"></i>
                            <span class="menu-label">PACU</span>
                        </a>
                    </li>
                    @endcan
                    {{-- OT Consumable Usage moved to unified "Inventory & Stock" menu --}}
                    @can('ot_cleaning_access')
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('ot.cleaning.*') ? ' active' : '' }}"
                            href="{{ route('ot.cleaning.index') }}">
                            <i class="bi bi-droplet-half"></i>
                            <span class="menu-label">Cleaning</span>
                        </a>
                    </li>
                    @endcan
                    @can('ot_documents_access')
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('ot.documents.*') ? ' active' : '' }}"
                            href="{{ route('ot.documents.index') }}">
                            <i class="bi bi-file-earmark-text"></i>
                            <span class="menu-label">Documents</span>
                        </a>
                    </li>
                    @endcan
                    @can('ot_emergency_access')
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('ot.emergency.*') ? ' active' : '' }}"
                            href="{{ route('ot.emergency.index') }}">
                            <i class="bi bi-exclamation-triangle"></i>
                            <span class="menu-label">Emergency</span>
                        </a>
                    </li>
                    @endcan
                    @can('ot_reports_access')
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('ot.reports.*') ? ' active' : '' }}"
                            href="{{ route('ot.reports.index') }}">
                            <i class="bi bi-bar-chart-line"></i>
                            <span class="menu-label">Reports</span>
                        </a>
                    </li>
                    @endcan
                    @can('ot_setup_access')
                    {{-- OT Master — nested submenu containing all OT master-data screens --}}
                    <li class="menu-item menu-arrow{{ request()->routeIs('ot.setup.*') || request()->routeIs('ot.rooms.*') || request()->routeIs('ot.teams.*') || request()->routeIs('ot.equipments.*') ? ' route-active' : '' }}">
                        <a class="menu-link{{ request()->routeIs('ot.setup.*') ? ' active' : '' }}"
                            href="javascript:void(0);" role="button">
                            <i class="bi bi-gear"></i>
                            <span class="menu-label">OT Master</span>
                        </a>
                        <ul class="menu-inner">
                            <li class="menu-item">
                                <a class="menu-link{{ request()->routeIs('ot.setup.index') ? ' active' : '' }}" href="{{ route('ot.setup.index') }}">
                                    <i class="bi bi-sliders"></i><span class="menu-label">Setup Overview</span>
                                </a>
                            </li>
                            @can('ot_room_access')
                            <li class="menu-item">
                                <a class="menu-link{{ request()->routeIs('ot.rooms.*') || request()->routeIs('ot.setup.rooms.*') ? ' active' : '' }}" href="{{ route('ot.rooms.index') }}">
                                    <i class="bi bi-door-closed"></i><span class="menu-label">Rooms</span>
                                </a>
                            </li>
                            @endcan
                            <li class="menu-item">
                                <a class="menu-link{{ request()->routeIs('ot.setup.equipments.*') ? ' active' : '' }}" href="{{ route('ot.setup.equipments.index') }}">
                                    <i class="bi bi-wrench"></i><span class="menu-label">Equipment</span>
                                </a>
                            </li>
                            @can('ot_team_access')
                            <li class="menu-item">
                                <a class="menu-link{{ request()->routeIs('ot.teams.*') ? ' active' : '' }}" href="{{ route('ot.teams.index') }}">
                                    <i class="bi bi-people"></i><span class="menu-label">Teams</span>
                                </a>
                            </li>
                            @endcan
                            <li class="menu-item">
                                <a class="menu-link{{ request()->routeIs('ot.setup.surgery-types.*') ? ' active' : '' }}" href="{{ route('ot.setup.surgery-types.index') }}">
                                    <i class="bi bi-clipboard"></i><span class="menu-label">Surgery Types</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link{{ request()->routeIs('ot.setup.surgery-categories.*') ? ' active' : '' }}" href="{{ route('ot.setup.surgery-categories.index') }}">
                                    <i class="bi bi-tags"></i><span class="menu-label">Surgery Categories</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link{{ request()->routeIs('ot.setup.anesthesia-types.*') ? ' active' : '' }}" href="{{ route('ot.setup.anesthesia-types.index') }}">
                                    <i class="bi bi-droplet"></i><span class="menu-label">Anesthesia Types</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link{{ request()->routeIs('ot.setup.consumables.*') ? ' active' : '' }}" href="{{ route('ot.setup.consumables.index') }}">
                                    <i class="bi bi-box-seam"></i><span class="menu-label">Consumables</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endcan
                </ul>
            </li>
            @endcanany

            {{-- CCU --}}
            <li class="menu-item menu-arrow{{ $ccuActive ? ' route-active' : '' }}">
                <a class="menu-link{{ $ccuActive ? ' active' : '' }}" href="javascript:void(0);" role="button">
                    <i class="bi bi-heart"></i>
                    <span class="menu-label">CCU</span>
                </a>

                <ul class="menu-inner">
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('icu.dashboard') && $isCcuView ? ' active' : '' }}"
                            href="{{ route('icu.dashboard', ['icu_type' => 'CCU']) }}">
                            <i class="bi bi-speedometer2"></i>
                            <span class="menu-label">Live Dashboard</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('icu.admissions.*') && $isCcuView ? ' active' : '' }}"
                            href="{{ route('icu.admissions.index', ['icu_type' => 'CCU']) }}">
                            <i class="bi bi-clipboard2-pulse"></i>
                            <span class="menu-label">Admissions</span>
                        </a>
                    </li>
                    {{-- CCU Equipment + CCU Packages moved to unified menus.
                         All package types now managed via Configuration → Package Management.
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('icu.packages.*') && $isCcuView ? ' active' : '' }}"
                            href="{{ route('icu.packages.index', ['icu_type' => 'CCU']) }}">
                            <i class="bi bi-box-seam"></i>
                            <span class="menu-label">CCU Packages</span>
                        </a>
                    </li>
                    --}}
                    @php
                        $ccuReportsActive = (request()->routeIs('icu.infection.reports') || request()->routeIs('icu.mortality.*') || request()->routeIs('icu.admissions.mortality.*')) && $isCcuView;
                    @endphp
                    <li class="menu-item menu-arrow{{ $ccuReportsActive ? ' route-active' : '' }}">
                        <a class="menu-link{{ $ccuReportsActive ? ' active' : '' }}"
                            href="javascript:void(0);" role="button">
                            <i class="bi bi-file-earmark-bar-graph"></i>
                            <span class="menu-label">Reports</span>
                        </a>
                        <ul class="menu-inner">
                            <li class="menu-item">
                                <a class="menu-link{{ request()->routeIs('icu.infection.reports') && $isCcuView ? ' active' : '' }}"
                                    href="{{ route('icu.infection.reports', ['icu_type' => 'CCU']) }}">
                                    <i class="bi bi-shield-exclamation"></i>
                                    <span class="menu-label">Infection</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link" href="javascript:void(0);">
                                    <i class="bi bi-box-arrow-right"></i>
                                    <span class="menu-label">Discharge</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link{{ (request()->routeIs('icu.mortality.*') || request()->routeIs('icu.admissions.mortality.*')) && $isCcuView ? ' active' : '' }}"
                                    href="{{ route('icu.mortality.index', ['icu_type' => 'CCU']) }}">
                                    <i class="bi bi-heartbreak"></i>
                                    <span class="menu-label">Mortality</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <div class="menu-heading d-flex align-items-center gap-2 px-3 py-2 mt-2 text-uppercase fw-semibold text-primary"
                        style="font-size: 10px; letter-spacing: .5px; border-bottom: 1px solid var(--bs-border-color);">
                        <i class="fi fi-rs-interrogation"></i>
                        <span class="menu-label">Clinical Management</span>
                    </div>

                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('icu.orders.manage') && $isCcuView ? ' active' : '' }}"
                            href="{{ route('icu.orders.manage', ['icu_type' => 'CCU']) }}">
                            <i class="bi bi-clipboard-check"></i>
                            <span class="menu-label">Order Management</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('icu.nursing-notes.manage') && $isCcuView ? ' active' : '' }}"
                            href="{{ route('icu.nursing-notes.manage', ['icu_type' => 'CCU']) }}">
                            <i class="bi bi-journal-text"></i>
                            <span class="menu-label">Nursing Notes</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('icu.intake-output.manage') && $isCcuView ? ' active' : '' }}"
                            href="{{ route('icu.intake-output.manage', ['icu_type' => 'CCU']) }}">
                            <i class="bi bi-droplet-half"></i>
                            <span class="menu-label">Intake/Output</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('icu.code-blue.*') && $isCcuView ? ' active' : '' }}"
                            href="{{ route('icu.code-blue.index', ['icu_type' => 'CCU']) }}">
                            <i class="bi bi-exclamation-octagon-fill text-danger"></i>
                            <span class="menu-label">Code Blue</span>
                        </a>
                    </li>
                </ul>
            </li>

            {{-- NICU --}}
            @canany(['nicu.dashboard.view', 'nicu.admission.view'])
            <li class="menu-item menu-arrow{{ request()->routeIs('nicu.*') ? ' route-active' : '' }}">
                <a class="menu-link{{ request()->routeIs('nicu.*') ? ' active' : '' }}" href="javascript:void(0);" role="button">
                    <i class="bi bi-emoji-smile"></i>
                    <span class="menu-label">NICU</span>
                </a>
                <ul class="menu-inner">
                    @can('nicu.dashboard.view')
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('nicu.dashboard') ? ' active' : '' }}"
                            href="{{ route('nicu.dashboard') }}">
                            <i class="bi bi-speedometer2"></i>
                            <span class="menu-label">Live Dashboard</span>
                        </a>
                    </li>
                    @endcan
                    @can('nicu.admission.view')
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('nicu.admissions.*') ? ' active' : '' }}"
                            href="{{ route('nicu.admissions.index') }}">
                            <i class="bi bi-clipboard2-heart"></i>
                            <span class="menu-label">Admissions</span>
                        </a>
                    </li>
                    @endcan
                    @can('nicu.resource.view')
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('nicu.resources.*') ? ' active' : '' }}"
                            href="{{ route('nicu.resources.index') }}">
                            <i class="bi bi-box"></i>
                            <span class="menu-label">Incubator / Warmer</span>
                        </a>
                    </li>
                    @endcan
                    @can('nicu.vital.view')
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('nicu.vitals.*') ? ' active' : '' }}"
                            href="{{ route('nicu.vitals.index') }}">
                            <i class="bi bi-activity"></i>
                            <span class="menu-label">Monitoring</span>
                        </a>
                    </li>
                    @endcan
                    @can('nicu.feeding.view')
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('nicu.feeding.*') ? ' active' : '' }}"
                            href="{{ route('nicu.feeding.index') }}">
                            <i class="bi bi-cup-hot"></i>
                            <span class="menu-label">Feeding & Nutrition</span>
                        </a>
                    </li>
                    @endcan
                    @can('nicu.growth.view')
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('nicu.growth.*') ? ' active' : '' }}"
                            href="{{ route('nicu.growth.index') }}">
                            <i class="bi bi-graph-up"></i>
                            <span class="menu-label">Growth & Charting</span>
                        </a>
                    </li>
                    @endcan
                    @can('nicu.medication.view')
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('nicu.medications.*') ? ' active' : '' }}"
                            href="{{ route('nicu.medications.index') }}">
                            <i class="bi bi-capsule-pill"></i>
                            <span class="menu-label">Medication (MAR)</span>
                        </a>
                    </li>
                    @endcan
                    @can('nicu.procedure.view')
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('nicu.procedures.*') ? ' active' : '' }}"
                            href="{{ route('nicu.procedures.index') }}">
                            <i class="bi bi-lightbulb"></i>
                            <span class="menu-label">Procedures</span>
                        </a>
                    </li>
                    @endcan
                    @can('nicu.infection.view')
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('nicu.infections.*') ? ' active' : '' }}"
                            href="{{ route('nicu.infections.index') }}">
                            <i class="bi bi-shield-exclamation"></i>
                            <span class="menu-label">Infection Control</span>
                        </a>
                    </li>
                    @endcan
                    @can('nicu.consent.view')
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('nicu.consents.*') ? ' active' : '' }}"
                            href="{{ route('nicu.consents.index') }}">
                            <i class="bi bi-file-earmark-check"></i>
                            <span class="menu-label">Parent Consent</span>
                        </a>
                    </li>
                    @endcan
                </ul>
            </li>
            @endcanany

            {{-- ───────── DIAGNOSTICS & THERAPY ───────── --}}
            <li class="menu-heading px-3 py-2 mt-3 text-uppercase fw-semibold text-muted small">Diagnostics &amp; Therapy</li>

            <li class="menu-item">
                <a class="menu-link{{ request()->routeIs('blood-bank.*') ? ' active' : '' }}"
                    href="{{ route('blood-bank.index') }}">
                    <i class="fi fi-rr-blood"></i>
                    <span class="menu-label">Blood Bank</span>
                </a>
            </li>

            {{-- Diagnostics — single mega-menu replacing separate Pathology/Radiology entries.
                 Filter shortcuts for every lab investigation type. --}}
            <li class="menu-item menu-arrow{{ request()->routeIs('pathology.*', 'radiology.*', 'diagnostics.*') ? ' route-active' : '' }}">
                <a class="menu-link{{ request()->routeIs('pathology.*', 'radiology.*', 'diagnostics.*') ? ' active' : '' }}"
                    href="javascript:void(0);" role="button">
                    <i class="fi fi-rr-microscope"></i>
                    <span class="menu-label">Diagnostics</span>
                </a>
                <ul class="menu-inner">
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('pathology.index') && !request('type') ? ' active' : '' }}"
                            href="{{ route('pathology.index') }}">
                            <i class="bi bi-list-ul"></i>
                            <span class="menu-label">All Orders</span>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li class="menu-heading px-3 small text-muted">By Test Type</li>
                    @php
                        $diagTypes = [
                            ['Pathology',             'pathology.index',  'eyedropper',  'info'],
                            ['Radiology',             'radiology.index',  'broadcast',   'primary'],
                            ['Microbiology',          null,               'bug',         'danger'],
                            ['Histopathology',        null,               'scissors',    'warning'],
                            ['Cytopathology',         null,               'circle',      'info'],
                            ['Immunology / Serology', null,               'shield-check','primary'],
                            ['Endocrinology',         null,               'droplet',     'success'],
                            ['Cardiology Diagnostics',null,               'heart-pulse', 'danger'],
                            ['Genetics & Molecular',  null,               'cpu',         'secondary'],
                        ];
                    @endphp
                    @foreach ($diagTypes as [$label, $route, $icon, $colour])
                        @if ($route && \Illuminate\Support\Facades\Route::has($route))
                            <li class="menu-item">
                                <a class="menu-link{{ request()->routeIs($route) ? ' active' : '' }}"
                                    href="{{ route($route) }}">
                                    <i class="bi bi-{{ $icon }} text-{{ $colour }}"></i>
                                    <span class="menu-label">{{ $label }}</span>
                                </a>
                            </li>
                        @else
                            <li class="menu-item">
                                <a class="menu-link"
                                    href="{{ route('pathology.index') }}?type={{ urlencode($label) }}">
                                    <i class="bi bi-{{ $icon }} text-{{ $colour }}"></i>
                                    <span class="menu-label">{{ $label }}</span>
                                </a>
                            </li>
                        @endif
                    @endforeach
                    <li><hr class="dropdown-divider my-1"></li>
                    <li class="menu-heading px-3 small text-muted">Master Data</li>
                    <li class="menu-item">
                        <a class="menu-link"
                            href="{{ route('lab-investigations.index') }}">
                            <i class="bi bi-list-check"></i>
                            <span class="menu-label">Investigation Master</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link"
                            href="{{ route('lab-investigation-types.index') }}">
                            <i class="bi bi-tag"></i>
                            <span class="menu-label">Investigation Types</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link"
                            href="{{ route('lab-investigation-categories.index') }}">
                            <i class="bi bi-tags"></i>
                            <span class="menu-label">Investigation Categories</span>
                        </a>
                    </li>
                </ul>
            </li>

            <li
                class="menu-item menu-arrow{{ isMultiRoute(['admin.medicines.', 'admin.pharmacy.']) ? ' route-active' : '' }}">
                <a class="menu-link{{ isMultiRoute(['admin.medicines.', 'admin.pharmacy.']) ? ' active' : '' }}"
                    href="javascript:void(0);" role="button">
                    <i class="bi bi-capsule"></i>
                    <span class="menu-label">Pharmacy</span>
                </a>

                <ul class="menu-inner">
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('admin.medicines.*') ? ' active' : '' }}"
                            href="{{ route('admin.medicines.index') }}">
                            <i class="bi bi-grid"></i>
                            <span class="menu-label">Overview</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('admin.pharmacy.drug-master') ? ' active' : '' }}"
                            href="{{ route('admin.pharmacy.drug-master') }}">
                            <i class="bi bi-journal-medical"></i>
                            <span class="menu-label">Drug Master</span>
                        </a>
                    </li>
                    {{-- Pharmacy Stock moved to unified "Inventory & Stock" menu --}}
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('admin.pharmacy.transactions*') ? ' active' : '' }}"
                            href="{{ route('admin.pharmacy.transactions') }}">
                            <i class="bi bi-arrow-left-right"></i>
                            <span class="menu-label">Transactions</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('admin.pharmacy.returns*') ? ' active' : '' }}"
                            href="{{ route('admin.pharmacy.returns') }}">
                            <i class="bi bi-arrow-return-left"></i>
                            <span class="menu-label">Returns</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('admin.pharmacy.controlled-drugs') ? ' active' : '' }}"
                            href="{{ route('admin.pharmacy.controlled-drugs') }}">
                            <i class="bi bi-shield-exclamation"></i>
                            <span class="menu-label">Controlled Drugs</span>
                        </a>
                    </li>
                </ul>
            </li>

            {{-- ───────── FINANCE & INSURANCE ───────── --}}
            <li class="menu-heading px-3 py-2 mt-3 text-uppercase fw-semibold text-muted small">Finance &amp; Insurance</li>

            @php $billingActive = request()->routeIs('billing.*') || request()->routeIs('ot.billing.*'); @endphp
            <li class="menu-item menu-arrow{{ $billingActive ? ' route-active' : '' }}">
                <a class="menu-link{{ $billingActive ? ' active' : '' }}" href="javascript:void(0);"
                    role="button">
                    <i class="fi fi-rr-file-invoice-dollar"></i>
                    <span class="menu-label">Billing (Legacy)</span>
                </a>

                <ul class="menu-inner">
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('billing.index') ? ' active' : '' }}"
                            href="{{ route('billing.index') }}">
                            <i class="fi fi-rr-dashboard"></i>
                            <span class="menu-label">Overview</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('billing.ipd-billing.*') ? ' active' : '' }}"
                            href="{{ route('billing.ipd-billing.index') }}">
                            <i class="fi fi-rr-hospital"></i>
                            <span class="menu-label">Ipd Billing</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('billing.opd-billing.*') ? ' active' : '' }}"
                            href="{{ route('billing.opd-billing.index') }}">
                            <i class="fi fi-rr-stethoscope"></i>
                            <span class="menu-label">OPD Billing</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('billing.category.*') && request()->route('category')==='emergency' ? ' active' : '' }}"
                            href="{{ route('billing.category.index', 'emergency') }}">
                            <i class="fas fa-heartbeat"></i>
                            <span class="menu-label">Emergency Billing</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('billing.category.*') && request()->route('category')==='pathology' ? ' active' : '' }}"
                            href="{{ route('billing.category.index', 'pathology') }}">
                            <i class="fi fi-rr-microscope"></i>
                            <span class="menu-label">Pathology Billing</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('billing.category.*') && request()->route('category')==='radiology' ? ' active' : '' }}"
                            href="{{ route('billing.category.index', 'radiology') }}">
                            <i class="fi fi-rr-x-ray"></i>
                            <span class="menu-label">Radiology Billing</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('billing.category.*') && request()->route('category')==='utility' ? ' active' : '' }}"
                            href="{{ route('billing.category.index', 'utility') }}">
                            <i class="fi fi-rr-bolt"></i>
                            <span class="menu-label">Utility Billing</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('billing.category.*') && request()->route('category')==='blood-bank' ? ' active' : '' }}"
                            href="{{ route('billing.category.index', 'blood-bank') }}">
                            <i class="fi fi-rr-blood"></i>
                            <span class="menu-label">Blood Bank Billing</span>
                        </a>
                    </li>
                    @can('ot_billing_access')
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('ot.billing.*') ? ' active' : '' }}"
                            href="{{ route('ot.billing.index') }}">
                            <i class="fi fi-rr-scalpel"></i>
                            <span class="menu-label">OT Billing</span>
                        </a>
                    </li>
                    @endcan
                </ul>
            </li>

            {{-- ───────── RECEPTION & SUPPORT ───────── --}}
            <li class="menu-heading px-3 py-2 mt-3 text-uppercase fw-semibold text-muted small">Reception &amp; Support</li>

            <li class="menu-item">
                <a class="menu-link{{ request()->routeIs('appointments.*', 'appointment-priorities.*', 'shifts.*', 'doctor-shifts.*', 'doctor-slots.*', 'patient-queue.*') ? ' active' : '' }}"
                    href="{{ route('appointments.index') }}">
                    <i class="fi fi-rr-calendar-clock"></i>
                    <span class="menu-label">Appointment</span>
                </a>
            </li>

            <li class="menu-item">
                <a class="menu-link{{ request()->routeIs('ambulance/requests.*') ? ' active' : '' }}"
                    href="{{ route('amb.requests.index') }}">
                    <i class="fas fa-list"></i>
                    <span class="menu-label">Ambulance Request</span>
                </a>
            </li>
            <li class="menu-item menu-arrow{{ request()->routeIs('er.*', 'amb.er.*') ? ' route-active' : '' }}">
                <a class="menu-link{{ request()->routeIs('er.*', 'amb.er.*') ? ' active' : '' }}"
                    href="javascript:void(0);" role="button">
                    <i class="fas fa-heartbeat text-danger"></i>
                    <span class="menu-label">ER Dashboard</span>
                </a>
                <ul class="menu-inner">
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('er.dashboard*') ? ' active' : '' }}"
                            href="{{ route('er.dashboard') }}">
                            <i class="bi bi-speedometer2"></i><span class="menu-label">Live Dashboard</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('er.board') ? ' active' : '' }}"
                            href="{{ route('er.board') }}">
                            <i class="bi bi-kanban"></i><span class="menu-label">Tracking Board</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('front_desk.er_registration*') ? ' active' : '' }}"
                            href="{{ route('front_desk.er_registration') }}">
                            <i class="bi bi-plus-lg"></i><span class="menu-label">New Registration</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('amb.er.incoming') ? ' active' : '' }}"
                            href="{{ route('amb.er.incoming') }}">
                            <i class="fas fa-ambulance"></i><span class="menu-label">Incoming Ambulances</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('billing.category.*') && request()->route('category') === 'emergency' ? ' active' : '' }}"
                            href="{{ route('billing.category.index', 'emergency') }}">
                            <i class="bi bi-receipt"></i><span class="menu-label">Emergency Billing</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="menu-item">
                <a class="menu-link{{ request()->routeIs('health-card.index') ? ' active' : '' }}"
                    href="{{ route('health-card.index') }}">
                    <i class="fas fa-id-card"></i>
                    <span class="menu-label">Card Management</span>
                </a>
            </li>

            {{-- ───────── CONFIGURATION ───────── --}}
            <li class="menu-heading px-3 py-2 mt-3 text-uppercase fw-semibold text-muted small">Configuration</li>

            <li
                class="menu-item menu-arrow{{ isMultiRoute(['patients.', 'doctors.', 'floor.', 'bed-groups.', 'bed-types.', 'beds.', 'designations.', 'departments.', 'specialists.', 'doctor-fees.', 'packages.', 'services.', 'lab-investigation-types.', 'lab-investigation-categories.', 'lab-investigations.', 'symptoms.', 'bb.blood-groups.', 'bb.components.', 'bb.temperature-rules.', 'bb.storage-locations.', 'bb.blood-bags.', 'bb.deferral-reasons.', 'bb.blood-donors.', 'appointment-priorities.', 'shifts.', 'doctor-shifts.', 'doctor-slots.']) ? ' route-active' : '' }}">
                <a class="menu-link{{ isMultiRoute(['patients.', 'doctors.', 'floor.', 'bed-groups.', 'bed-types.', 'beds.', 'designations.', 'departments.', 'specialists.', 'doctor-fees.', 'packages.', 'services.', 'lab-investigation-types.', 'lab-investigation-categories.', 'lab-investigations.', 'symptoms.', 'bb.blood-groups.', 'bb.components.', 'bb.temperature-rules.', 'bb.storage-locations.', 'bb.blood-bags.', 'bb.deferral-reasons.', 'bb.blood-donors.', 'appointment-priorities.', 'shifts.', 'doctor-shifts.', 'doctor-slots.']) ? ' active' : '' }}"
                    href="javascript:void(0);" role="button">
                    <i class="fi fi-rr-database"></i>
                    <span class="menu-label">Master Data Setup</span>
                </a>

                <ul class="menu-inner">
                    <li class="menu-item">
                        <a class="menu-link{{ isRouteSection('patients.') ? ' active' : '' }}"
                            href="{{ route('patients.index') }}">
                            <i class="fi fi-rr-user"></i>
                            <span class="menu-label">Patient</span>
                        </a>
                    </li>

                    <li class="menu-item">
                        <a class="menu-link{{ isRouteSection('doctors.') ? ' active' : '' }}"
                            href="{{ route('doctors.index') }}">
                            <i class="fi fi-rr-user-md"></i>
                            <span class="menu-label">Doctor</span>
                        </a>
                    </li>

                    <li class="menu-item">
                        <a class="menu-link{{ isRouteSection('doctor-fees.') ? ' active' : '' }}"
                            href="{{ route('doctor-fees.index') }}">
                            <i class="fi fi-rr-money-check"></i>
                            <span class="menu-label">Doctor Fees</span>
                        </a>
                    </li>

                    <li class="menu-item">
                        <a class="menu-link {{ request()->routeIs('floor.*', 'bed-groups.*', 'bed-types.*', 'beds.*') ? 'active' : '' }}"
                            href="{{ route('floors.index') }}">
                            <i class="fi fi-rr-bed"></i>
                            <span class="menu-label">Bed</span>
                        </a>
                    </li>

                    <li class="menu-item">
                        <a class="menu-link {{ request()->routeIs('operation-types.*', 'operations.*', 'operation-procedures.*', 'operation-theatres.*') ? 'active' : '' }}"
                            href="{{ route('operation-types.index') }}">
                            <i class="fi fi-rr-scalpel"></i>
                            <span class="menu-label">Operation</span>
                        </a>
                    </li>

                    <li class="menu-item">
                        <a class="menu-link {{ request()->routeIs('departments.*', 'designations.*', 'specialists.*') ? 'active' : '' }}"
                            href="{{ route('departments.index') }}">
                            <i class="fi fi-rr-briefcase"></i>
                            <span class="menu-label">HRM</span>
                        </a>
                    </li>
                    {{-- Appointment Config sub-menu — was a single link, now expanded so
                         each master can be reached directly --}}
                    <li class="menu-item menu-arrow{{ request()->routeIs('appointment-priorities.*', 'shifts.*', 'doctor-shifts.*', 'doctor-slots.*') ? ' route-active' : '' }}">
                        <a class="menu-link{{ request()->routeIs('appointment-priorities.*', 'shifts.*', 'doctor-shifts.*', 'doctor-slots.*') ? ' active' : '' }}"
                            href="javascript:void(0);" role="button">
                            <i class="fi fi-rr-calendar-clock"></i>
                            <span class="menu-label">Appointment Config</span>
                        </a>
                        <ul class="menu-inner">
                            <li class="menu-item">
                                <a class="menu-link{{ request()->routeIs('shifts.*') ? ' active' : '' }}"
                                    href="{{ route('shifts.index') }}">
                                    <i class="bi bi-clock-history"></i>
                                    <span class="menu-label">Shifts</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link{{ request()->routeIs('doctor-shifts.*') ? ' active' : '' }}"
                                    href="{{ route('doctor-shifts.index') }}">
                                    <i class="bi bi-person-workspace"></i>
                                    <span class="menu-label">Doctor ↔ Shift Assignment</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link{{ request()->routeIs('doctor-slots.*') ? ' active' : '' }}"
                                    href="{{ route('doctor-slots.index') }}">
                                    <i class="bi bi-calendar3-range"></i>
                                    <span class="menu-label">Doctor Slot Times</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link{{ request()->routeIs('appointment-priorities.*') ? ' active' : '' }}"
                                    href="{{ route('appointment-priorities.index') }}">
                                    <i class="bi bi-flag"></i>
                                    <span class="menu-label">Appointment Priorities</span>
                                </a>
                            </li>
                        </ul>
                    </li>


                    <li class="menu-item">
                        <a class="menu-link {{ request()->routeIs('charges.*', 'charges.*', 'charges.*') ? 'active' : '' }}"
                            href="{{ route('admin.charges.index') }}">
                            <i class="fi fi-rr-briefcase"></i>
                            <span class="menu-label">Charges</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link {{ request()->is('admin/medicine-categories*', 'admin/medicine-units*', 'admin/medicine-generics*', 'admin/companies*', 'admin/suppliers*', 'admin/medical-groups*', 'admin/medicines*') ? 'active' : '' }}"
                            href="{{ route('admin.medicine-categories.index') }}">
                            <i class="bi bi-capsule"></i>
                            <span class="menu-label">Pharmacy Master</span>
                        </a>
                    </li>

                    <li class="menu-item">
                        <a class="menu-link {{ request()->routeIs(
                            'bb.blood-groups.*',
                            'bb.components.*',
                            'bb.temperature-rules.*',
                            'bb.storage-locations.*',
                            'bb.blood-bags.*',
                            'bb.deferral-reasons.*',
                            'bb.blood-donors.*',
                        )
                            ? 'active'
                            : '' }}"
                            href="{{ route('bb.blood-groups.index') }}">
                            <i class="fi fi-rr-briefcase"></i>
                            <span class="menu-label">Blood Bank Master</span>
                        </a>
                    </li>

                    <li class="menu-item">
                        <a class="menu-link {{ request()->routeIs('lab-investigation-types.*', 'lab-investigation-categories.*', 'lab-investigations.*') ? 'active' : '' }}"
                            href="{{ route('lab-investigation-types.index') }}">
                            <i class="fi fi-rr-flask"></i>
                            <span class="menu-label">Lab Investigation</span>
                        </a>
                    </li>

                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('symptoms.*') ? ' active' : '' }}"
                            href="{{ route('symptoms.index') }}">
                            <i class="fi fi-rr-heart-rate"></i>
                            <span class="menu-label">Symptoms</span>
                        </a>
                    </li>
                    {{-- Legacy "Services" master deprecated — all services now live in Service Catalog
                         under Configuration → Service Charge. Hidden to avoid duplication.
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('services.*') ? ' active' : '' }}"
                            href="{{ route('services.index') }}">
                            <i class="fi fi-rr-list-check"></i>
                            <span class="menu-label">Services</span>
                        </a>
                    </li>
                    --}}
                    <li class="menu-item">
                        <a class="menu-link{{ request()->is('amb/ambulances*') ? ' active' : '' }}"
                            href="{{ route('amb.ambulances.index') }}">
                            <i class="fas fa-ambulance"></i>
                            <span class="menu-label">Ambulance Vehicle</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link{{ request()->is('amb/drivers*') ? ' active' : '' }}"
                            href="{{ route('amb.drivers.index') }}">
                            <i class="fas fa-car"></i>
                            <span class="menu-label">Ambulance Driver</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link{{ request()->is('amb/paramedics*') ? ' active' : '' }}"
                            href="{{ route('amb.paramedics.index') }}">
                            <i class="fas fa-user-md"></i>
                            <span class="menu-label">Paramedic</span>
                        </a>
                    </li>

                    <li class="menu-item menu-arrow{{ request()->routeIs('packages.*') ? ' route-active' : '' }}">
                        <a class="menu-link{{ request()->routeIs('packages.*') ? ' active' : '' }}"
                            href="javascript:void(0);" role="button">
                            <i class="fi fi-rr-box-open"></i>
                            <span class="menu-label">Package Management</span>
                        </a>
                        <ul class="menu-inner">
                            <li class="menu-item">
                                <a class="menu-link{{ request()->routeIs('packages.index') && !request('type') ? ' active' : '' }}"
                                    href="{{ route('packages.index') }}">
                                    <i class="bi bi-list-ul"></i><span class="menu-label">All Packages</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link{{ request()->routeIs('packages.create') ? ' active' : '' }}"
                                    href="{{ route('packages.create') }}">
                                    <i class="bi bi-plus-lg"></i><span class="menu-label">Create Package</span>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li class="menu-heading px-3 small text-muted">Filter by Type</li>
                            @php
                                $typeFilters = [
                                    'IPD'        => ['hospital',     'IPD Packages'],
                                    'OPD'        => ['stethoscope',  'OPD Packages'],
                                    'OT'         => ['scissors',     'OT / Surgery Packages'],
                                    'ICU'        => ['heart-pulse',  'ICU Packages'],
                                    'CCU'        => ['heart',        'CCU Packages'],
                                    'NICU'       => ['emoji-smile',  'NICU Packages'],
                                    'MATERNITY'  => ['gift',         'Maternity Packages'],
                                    'PATHOLOGY'  => ['eyedropper',   'Pathology Packages'],
                                    'RADIOLOGY'  => ['broadcast',    'Radiology Packages'],
                                    'DIAGNOSTIC' => ['activity',     'Diagnostic / Checkup'],
                                ];
                            @endphp
                            @foreach ($typeFilters as $typeCode => [$icon, $label])
                                <li class="menu-item">
                                    <a class="menu-link{{ request('type') === $typeCode ? ' active' : '' }}"
                                        href="{{ route('packages.index', ['type' => $typeCode]) }}">
                                        <i class="bi bi-{{ $icon }}"></i><span class="menu-label">{{ $label }}</span>
                                    </a>
                                </li>
                            @endforeach
                            <li><hr class="dropdown-divider my-1"></li>
                            <li class="menu-heading px-3 small text-muted">Reports</li>
                            <li class="menu-item">
                                <a class="menu-link{{ request()->routeIs('packages.reports.index') ? ' active' : '' }}"
                                    href="{{ route('packages.reports.index') }}">
                                    <i class="bi bi-file-earmark-bar-graph"></i><span class="menu-label">Reports Overview</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link{{ request()->routeIs('packages.reports.utilization') ? ' active' : '' }}"
                                    href="{{ route('packages.reports.utilization') }}">
                                    <i class="bi bi-graph-up"></i><span class="menu-label">Utilization</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link{{ request()->routeIs('packages.reports.revenue') ? ' active' : '' }}"
                                    href="{{ route('packages.reports.revenue') }}">
                                    <i class="bi bi-cash-stack"></i><span class="menu-label">Revenue Report</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link{{ request()->routeIs('packages.reports.expiry') ? ' active' : '' }}"
                                    href="{{ route('packages.reports.expiry') }}">
                                    <i class="bi bi-exclamation-triangle text-warning"></i><span class="menu-label">Expiry Alerts</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    {{-- Service Catalog is now back-end only — synced from /admin/charges.
                         Use Hospital Charges Setup to manage all pricing items. --}}
                    {{-- @can('service_charge.view')
                        <li class="menu-item">
                            <a class="menu-link{{ request()->routeIs('service-charge.*') ? ' active' : '' }}"
                                href="{{ route('service-charge.catalog.index') }}">
                                <i class="fi fi-rr-coins"></i>
                                <span class="menu-label">Service Catalog</span>
                            </a>
                        </li>
                    @endcan --}}

                    {{-- Enterprise master-data items consolidated here so the
                         user has one central "Master Data Setup" hub.
                         (Inventory Items + Warehouses moved to the dedicated
                          "Inventory & Stock" menu below.) --}}
                    @can('insurance.payer.view')
                        <li class="menu-item">
                            <a class="menu-link{{ request()->routeIs('insurance.payers.*') ? ' active' : '' }}"
                                href="{{ route('insurance.payers.index') }}">
                                <i class="fi fi-rr-shield-check"></i>
                                <span class="menu-label">Insurance Payers</span>
                            </a>
                        </li>
                    @endcan
                    @can('accounting.coa.view')
                        <li class="menu-item">
                            <a class="menu-link{{ request()->routeIs('accounting.coa.*') ? ' active' : '' }}"
                                href="{{ route('accounting.coa.index') }}">
                                <i class="fi fi-rr-balance-scale"></i>
                                <span class="menu-label">Chart of Accounts</span>
                            </a>
                        </li>
                    @endcan
                    @can('hr.employee.view')
                        <li class="menu-item">
                            <a class="menu-link{{ request()->routeIs('hr.employees.*') ? ' active' : '' }}"
                                href="{{ route('hr.employees.index') }}">
                                <i class="fi fi-rr-user-tie"></i>
                                <span class="menu-label">Employees</span>
                            </a>
                        </li>
                    @endcan
                    @can('organization.view')
                        <li class="menu-item">
                            <a class="menu-link{{ request()->routeIs('organizations.*') ? ' active' : '' }}"
                                href="{{ route('organizations.index') }}">
                                <i class="fi fi-rr-building"></i>
                                <span class="menu-label">Organizations</span>
                            </a>
                        </li>
                    @endcan
                    @can('branch.view')
                        <li class="menu-item">
                            <a class="menu-link{{ request()->routeIs('branches.*') ? ' active' : '' }}"
                                href="{{ route('branches.index') }}">
                                <i class="fi fi-rr-marker"></i>
                                <span class="menu-label">Branches</span>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>

            {{-- ───────── INVENTORY & STOCK (unified across modules) ───────── --}}
            @canany(['inventory.item.view', 'inventory.warehouse.view', 'inventory.stock.view'])
                @php $invActive = request()->is('inventory*', 'admin/pharmacy/inventory*', 'ot/inventory*', 'icu/equipment*', 'ot/setup/equipments*'); @endphp
                <li class="menu-heading px-3 py-2 mt-3 text-uppercase fw-semibold text-muted small">Inventory &amp; Stock</li>

                <li class="menu-item menu-arrow{{ $invActive ? ' route-active' : '' }}">
                    <a class="menu-link{{ $invActive ? ' active' : '' }}" href="javascript:void(0);" role="button">
                        <i class="fi fi-rr-warehouse-alt"></i>
                        <span class="menu-label">Inventory &amp; Stock</span>
                    </a>
                    <ul class="menu-inner">
                        {{-- Live KPI hub --}}
                        <li class="menu-item">
                            <a class="menu-link{{ request()->routeIs('admin.centers.inventory') ? ' active' : '' }}"
                                href="{{ route('admin.centers.inventory') }}">
                                <i class="bi bi-speedometer2 text-primary"></i>
                                <span class="menu-label">Inventory Dashboard</span>
                            </a>
                        </li>

                        <li><hr class="dropdown-divider my-1"></li>
                        <li class="menu-heading px-3 small text-muted">Master Data</li>

                        @can('inventory.item.view')
                            <li class="menu-item">
                                <a class="menu-link{{ request()->routeIs('inventory.items.*') ? ' active' : '' }}"
                                    href="{{ route('inventory.items.index') }}">
                                    <i class="bi bi-box"></i>
                                    <span class="menu-label">All Items</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link"
                                    href="{{ route('inventory.items.index') }}?type=medicine">
                                    <i class="bi bi-capsule"></i>
                                    <span class="menu-label">— Medicines</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link"
                                    href="{{ route('inventory.items.index') }}?type=consumable">
                                    <i class="bi bi-box-seam"></i>
                                    <span class="menu-label">— Consumables</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link"
                                    href="{{ route('inventory.items.index') }}?type=asset">
                                    <i class="bi bi-tools"></i>
                                    <span class="menu-label">— Equipment / Assets</span>
                                </a>
                            </li>
                        @endcan

                        @can('inventory.warehouse.view')
                            <li class="menu-item">
                                <a class="menu-link{{ request()->routeIs('inventory.warehouses.*') ? ' active' : '' }}"
                                    href="{{ route('inventory.warehouses.index') }}">
                                    <i class="fi fi-rr-warehouse-alt"></i>
                                    <span class="menu-label">Warehouses</span>
                                </a>
                            </li>
                        @endcan

                        <li><hr class="dropdown-divider my-1"></li>
                        <li class="menu-heading px-3 small text-muted">Movements & Operations</li>

                        @can('inventory.stock.view')
                            <li class="menu-item">
                                <a class="menu-link{{ request()->routeIs('inventory.movements.*') ? ' active' : '' }}"
                                    href="{{ route('inventory.movements.index') }}">
                                    <i class="fi fi-rr-list-check"></i>
                                    <span class="menu-label">Stock Ledger</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link{{ request()->routeIs('inventory.movements.create') ? ' active' : '' }}"
                                    href="{{ route('inventory.movements.create') }}">
                                    <i class="bi bi-plus-square"></i>
                                    <span class="menu-label">Stock In / Out</span>
                                </a>
                            </li>
                        @endcan

                        <li><hr class="dropdown-divider my-1"></li>
                        <li class="menu-heading px-3 small text-muted">Module-Specific Stock</li>

                        <li class="menu-item">
                            <a class="menu-link{{ request()->routeIs('admin.pharmacy.inventory') ? ' active' : '' }}"
                                href="{{ route('admin.pharmacy.inventory') }}">
                                <i class="bi bi-capsule text-info"></i>
                                <span class="menu-label">Pharmacy Stock</span>
                            </a>
                        </li>
                        @can('ot_inventory_access')
                            <li class="menu-item">
                                <a class="menu-link{{ request()->routeIs('ot.inventory.*') ? ' active' : '' }}"
                                    href="{{ route('ot.inventory.index') }}">
                                    <i class="bi bi-boxes text-warning"></i>
                                    <span class="menu-label">OT Consumable Usage</span>
                                </a>
                            </li>
                        @endcan

                        <li><hr class="dropdown-divider my-1"></li>
                        <li class="menu-heading px-3 small text-muted">Equipment Registry</li>

                        <li class="menu-item">
                            <a class="menu-link{{ request()->routeIs('admin.centers.equipment') ? ' active' : '' }}"
                                href="{{ route('admin.centers.equipment') }}">
                                <i class="bi bi-plug text-primary"></i>
                                <span class="menu-label">Equipment Center</span>
                            </a>
                        </li>
                        @can('ot_setup_access')
                            <li class="menu-item">
                                <a class="menu-link{{ request()->routeIs('ot.setup.equipments.*') ? ' active' : '' }}"
                                    href="{{ route('ot.setup.equipments.index') }}">
                                    <i class="bi bi-wrench"></i>
                                    <span class="menu-label">— OT Equipment</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link{{ request()->routeIs('ot.setup.consumables.*') ? ' active' : '' }}"
                                    href="{{ route('ot.setup.consumables.index') }}">
                                    <i class="bi bi-box-seam"></i>
                                    <span class="menu-label">— OT Consumables Master</span>
                                </a>
                            </li>
                        @endcan
                        <li class="menu-item">
                            <a class="menu-link{{ request()->routeIs('icu.equipment.*') && (! request('icu_type') || request('icu_type') === 'ICU') ? ' active' : '' }}"
                                href="{{ route('icu.equipment.index', ['icu_type' => 'ICU']) }}">
                                <i class="bi bi-cpu"></i>
                                <span class="menu-label">— ICU Equipment</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a class="menu-link{{ request()->routeIs('icu.equipment.*') && request('icu_type') === 'CCU' ? ' active' : '' }}"
                                href="{{ route('icu.equipment.index', ['icu_type' => 'CCU']) }}">
                                <i class="bi bi-cpu-fill"></i>
                                <span class="menu-label">— CCU Equipment</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a class="menu-link{{ request()->routeIs('icu.equipment.*') && request('icu_type') === 'NICU' ? ' active' : '' }}"
                                href="{{ route('icu.equipment.index', ['icu_type' => 'NICU']) }}">
                                <i class="bi bi-emoji-smile"></i>
                                <span class="menu-label">— NICU Equipment</span>
                            </a>
                        </li>
                    </ul>
                </li>
            @endcanany

            {{-- ───────── ANALYTICS & SHORTCUTS ───────── --}}
            <li class="menu-heading px-3 py-2 mt-3 text-uppercase fw-semibold text-muted small">Analytics &amp; Shortcuts</li>

            @canany(['dashboard.executive.view', 'dashboard.operational.view'])
                <li class="menu-item">
                    <a class="menu-link{{ request()->routeIs('insight.*') ? ' active' : '' }}"
                        href="{{ route('insight.dashboard') }}">
                        <i class="fi fi-rr-chart-pie-alt"></i>
                        <span class="menu-label">Insight Dashboard</span>
                    </a>
                </li>
            @endcanany

            {{-- "Bills" and "Insurance Claims" already live under the
                 "Finance & Insurance" section above — removed from here
                 to eliminate the duplicate sidebar entry flagged by the
                 menu audit. --}}

            {{-- Stock Ledger lives under the unified "Inventory & Stock" menu --}}

            @hasAnyPermission(['user_access', 'module_access', 'role_access', 'setting_access'])
                {{-- <li class="menu-heading">
                    <span class="menu-label">Administration</span>
                </li> --}}

                <li
                    class="menu-item menu-arrow{{ isMultiRoute(['users.', 'modules.', 'roles.', 'settings.', 'activity-logs.']) ? ' route-active' : '' }}">
                    <a class="menu-link{{ isMultiRoute(['users.', 'modules.', 'roles.', 'settings.', 'activity-logs.']) ? ' active' : '' }}"
                        href="javascript:void(0);" role="button">
                        <i class="fi fi-rr-settings-sliders"></i>
                        <span class="menu-label">System</span>
                    </a>

                    <ul class="menu-inner">
                        @hasOnlyPermission('user_access')
                            <li class="menu-item">
                                <a class="menu-link{{ isRouteSection('users.') ? ' active' : '' }}"
                                    href="{{ route('users.index') }}">
                                    <i class="fi fi-rr-users"></i>
                                    <span class="menu-label">Users</span>
                                </a>
                            </li>
                        @endHasOnlyPermission

                        @hasOnlyPermission('module_access')
                            <li class="menu-item">
                                <a class="menu-link{{ isRouteSection('modules.') ? ' active' : '' }}"
                                    href="{{ route('modules.index') }}">
                                    <i class="fi fi-rr-boxes"></i>
                                    <span class="menu-label">Modules</span>
                                </a>
                            </li>
                        @endHasOnlyPermission

                        @hasOnlyPermission('role_access')
                            <li class="menu-item">
                                <a class="menu-link{{ isRouteSection('roles.') ? ' active' : '' }}"
                                    href="{{ route('roles.index') }}">
                                    <i class="fi fi-rr-briefcase"></i>
                                    <span class="menu-label">Roles</span>
                                </a>
                            </li>
                        @endHasOnlyPermission

                        @hasOnlyPermission('setting_access')
                            <li class="menu-item">
                                <a class="menu-link{{ isRouteSection('settings.') ? ' active' : '' }}"
                                    href="{{ route('settings.index') }}">
                                    <i class="fi fi-rr-settings"></i>
                                    <span class="menu-label">Settings</span>
                                </a>
                            </li>

                            <li class="menu-item">
                                <a class="menu-link{{ request()->routeIs('activity-logs.index') ? ' active' : '' }}"
                                    href="{{ route('activity-logs.index') }}">
                                    <i class="fi fi-rr-time-past"></i>
                                    <span class="menu-label">Activity Logs</span>
                                </a>
                            </li>
                        @endHasOnlyPermission
                    </ul>
                </li>
            @endHasAnyPermission
        </ul>
    </nav>

    {{-- <div class="app-footer">
        <a href="pages/faq.html" class="btn btn-outline-light waves-effect btn-shadow btn-app-nav w-100">
            <i class="fi fi-rs-interrogation text-primary"></i>
            <span class="nav-text">Help and Support</span>
        </a>
    </div> --}}
</aside>
<!-- end::GXON Sidebar Menu -->
