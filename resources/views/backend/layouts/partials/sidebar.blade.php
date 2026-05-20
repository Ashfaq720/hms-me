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
            <li class="menu-item">
                <a class="menu-link{{ request()->routeIs('dashboard') ? ' active' : '' }}"
                    href="{{ route('dashboard') }}">
                    <i class="fi fi-rr-dashboard"></i>
                    <span class="menu-label">Dashboard</span>
                </a>
            </li>

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
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('icu.equipment.*') && !$isCcuView ? ' active' : '' }}"
                            href="{{ route('icu.equipment.index', ['icu_type' => 'ICU']) }}">
                            <i class="bi bi-gear"></i>
                            <span class="menu-label">Equipment</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('icu.packages.*') && !$isCcuView ? ' active' : '' }}"
                            href="{{ route('icu.packages.index', ['icu_type' => 'ICU']) }}">
                            <i class="bi bi-box-seam"></i>
                            <span class="menu-label">Packages</span>
                        </a>
                    </li>
                    <li
                        class="menu-item menu-arrow{{ request()->routeIs('icu.infection.reports') && !$isCcuView ? ' route-active' : '' }}">
                        <a class="menu-link{{ request()->routeIs('icu.infection.reports') && !$isCcuView ? ' active' : '' }}"
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
                                <a class="menu-link" href="javascript:void(0);">
                                    <i class="bi bi-heartbreak"></i>
                                    <span class="menu-label">Mortality</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link" href="javascript:void(0);">
                                    <i class="bi bi-gear"></i>
                                    <span class="menu-label">Equipment</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link" href="javascript:void(0);">
                                    <i class="bi bi-exclamation-octagon"></i>
                                    <span class="menu-label">Code Blue</span>
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
                        <a class="menu-link" href="javascript:void(0);">
                            <i class="bi bi-journal-text"></i>
                            <span class="menu-label">Nursing Notes</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="javascript:void(0);">
                            <i class="bi bi-droplet-half"></i>
                            <span class="menu-label">Intake/Output</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="javascript:void(0);">
                            <i class="bi bi-heart-pulse"></i>
                            <span class="menu-label">Care Plan</span>
                        </a>
                    </li>
                </ul>

            </li>

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
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('icu.equipment.*') && $isCcuView ? ' active' : '' }}"
                            href="{{ route('icu.equipment.index', ['icu_type' => 'CCU']) }}">
                            <i class="bi bi-gear"></i>
                            <span class="menu-label">Equipment</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('icu.packages.*') && $isCcuView ? ' active' : '' }}"
                            href="{{ route('icu.packages.index', ['icu_type' => 'CCU']) }}">
                            <i class="bi bi-box-seam"></i>
                            <span class="menu-label">Packages</span>
                        </a>
                    </li>
                    <li
                        class="menu-item menu-arrow{{ request()->routeIs('icu.infection.reports') && $isCcuView ? ' route-active' : '' }}">
                        <a class="menu-link{{ request()->routeIs('icu.infection.reports') && $isCcuView ? ' active' : '' }}"
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
                                <a class="menu-link" href="javascript:void(0);">
                                    <i class="bi bi-heartbreak"></i>
                                    <span class="menu-label">Mortality</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link" href="javascript:void(0);">
                                    <i class="bi bi-gear"></i>
                                    <span class="menu-label">Equipment</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link" href="javascript:void(0);">
                                    <i class="bi bi-exclamation-octagon"></i>
                                    <span class="menu-label">Code Blue</span>
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
                        <a class="menu-link" href="javascript:void(0);">
                            <i class="bi bi-journal-text"></i>
                            <span class="menu-label">Nursing Notes</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="javascript:void(0);">
                            <i class="bi bi-droplet-half"></i>
                            <span class="menu-label">Intake/Output</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="javascript:void(0);">
                            <i class="bi bi-heart-pulse"></i>
                            <span class="menu-label">Care Plan</span>
                        </a>
                    </li>
                </ul>
            </li>

            <li class="menu-item">
                <a class="menu-link{{ request()->routeIs('blood-bank.*') ? ' active' : '' }}"
                    href="{{ route('blood-bank.index') }}">
                    <i class="fi fi-rr-blood"></i>
                    <span class="menu-label">Blood Bank</span>
                </a>
            </li>

            <li class="menu-item">
                <a class="menu-link{{ request()->routeIs('pathology.*') ? ' active' : '' }}"
                    href="{{ route('pathology.index') }}">
                    <i class="fi fi-rr-microscope"></i>
                    <span class="menu-label">Pathology</span>
                </a>
            </li>

            <li class="menu-item">
                <a class="menu-link{{ request()->routeIs('radiology.*') ? ' active' : '' }}"
                    href="{{ route('radiology.index') }}">
                    <i class="fi fi-rr-x-ray"></i>
                    <span class="menu-label">Radiology</span>
                </a>
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
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('admin.pharmacy.inventory') ? ' active' : '' }}"
                            href="{{ route('admin.pharmacy.inventory') }}">
                            <i class="bi bi-box-seam"></i>
                            <span class="menu-label">Inventory</span>
                        </a>
                    </li>
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

            <li class="menu-item menu-arrow{{ request()->routeIs('billing.*') ? ' route-active' : '' }}">
                <a class="menu-link{{ request()->routeIs('billing.*') ? ' active' : '' }}" href="javascript:void(0);"
                    role="button">
                    <i class="fi fi-rr-file-invoice-dollar"></i>
                    <span class="menu-label">Billing</span>
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
                        <a class="menu-link" href="javascript:void(0);">
                            <i class="fas fa-heartbeat"></i>
                            <span class="menu-label">Emergency Billing</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="javascript:void(0);">
                            <i class="fi fi-rr-microscope"></i>
                            <span class="menu-label">Pathology Billing</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="javascript:void(0);">
                            <i class="fi fi-rr-x-ray"></i>
                            <span class="menu-label">Radiology Billing</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="javascript:void(0);">
                            <i class="fi fi-rr-bolt"></i>
                            <span class="menu-label">Utility Billing</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="javascript:void(0);">
                            <i class="fi fi-rr-blood"></i>
                            <span class="menu-label">Blood Bank Billing</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="javascript:void(0);">
                            <i class="fi fi-rr-scalpel"></i>
                            <span class="menu-label">OT Billing</span>
                        </a>
                    </li>
                </ul>
            </li>

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
            <li class="menu-item">
                <a class="menu-link{{ request()->routeIs('ambulance/er/incoming.*') ? ' active' : '' }}"
                    href="{{ route('amb.er.incoming') }}">
                    <i class="fas fa-heartbeat"></i>
                    <span class="menu-label">ER Dashboard</span>
                </a>
            </li>
            <li class="menu-item">
                <a class="menu-link{{ request()->routeIs('health-card.index') ? ' active' : '' }}"
                    href="{{ route('health-card.index') }}">
                    <i class="fas fa-id-card"></i>
                    <span class="menu-label">Card Management</span>
                </a>
            </li>

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
                    <li class="menu-item">
                        <a class="menu-link {{ request()->routeIs('appointment-priorities.*', 'shifts.*', 'doctor-shifts.*', 'doctor-slots.*') ? 'active' : '' }}"
                            href="{{ route('doctor-slots.index') }}">
                            <i class="fi fi-rr-calendar-clock"></i>
                            <span class="menu-label">Appointment</span>
                        </a>
                    </li>


                    <li class="menu-item">
                        <a class="menu-link {{ request()->routeIs('charges.*', 'charges.*', 'charges.*') ? 'active' : '' }}"
                            href="{{ route('admin.charges.index') }}">
                            <i class="fi fi-rr-briefcase"></i>
                            <span class="menu-label">Charges</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link {{ request()->routeIs('admin.medicine-categories.*', 'admin.medicine-categories.*', 'admin.medicine-categories.*') ? 'active' : '' }}"
                            href="{{ route('admin.medicine-categories.index') }}">
                            <i class="bi bi-capsule"></i>
                            <span class="menu-label">Pharmacy</span>
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
                            <span class="menu-label">Blood Bank</span>
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
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('services.*') ? ' active' : '' }}"
                            href="{{ route('services.index') }}">
                            <i class="fi fi-rr-list-check"></i>
                            <span class="menu-label">Services</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('ambulance/ambulances.*') ? ' active' : '' }}"
                            href="{{ route('amb.ambulances.index') }}">
                            <i class="fas fa-ambulance"></i>
                            <span class="menu-label">Ambulance</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('ambulance/drivers.*') ? ' active' : '' }}"
                            href="{{ route('amb.drivers.index') }}">
                            <i class="fas fa-car"></i>
                            <span class="menu-label">Driver</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('ambulance/paramedics.*') ? ' active' : '' }}"
                            href="{{ route('amb.paramedics.index') }}">
                            <i class="fas fa-user-md"></i>
                            <span class="menu-label">Paramedic</span>
                        </a>
                    </li>

                    <li class="menu-item">
                        <a class="menu-link{{ request()->routeIs('packages.*') ? ' active' : '' }}"
                            href="{{ route('packages.index') }}">
                            <i class="fi fi-rr-box-open"></i>
                            <span class="menu-label">Packages</span>
                        </a>
                    </li>
                </ul>
            </li>

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
