@extends('backend.layouts.master')

@section('title', 'Dashboard')

@section('content')
    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div class="clearfix">
                <h1 class="app-page-title">Dashboard</h1>
                <span>Mon, Aug 01, 2024 - Sep 01, 2024 </span>
            </div>
            <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal"
                data-bs-target="#addEmployeeModal">
                <i class="fi fi-rr-plus me-1"></i> Add Employee
            </button>
        </div>

        <div class="row">
            <div class="col-xxl-9">
                <div class="row">
                    <div class="col-6 col-md-4 col-lg">
                        <div class="card bg-secondary bg-opacity-05 shadow-none border-0">
                            <div class="card-body">
                                <div class="avatar bg-secondary shadow-secondary rounded-circle text-white mb-3">
                                    <i class="fi fi-sr-users"></i>
                                </div>
                                <h3>1206</h3>
                                <h6 class="mb-0">Total Employee</h6>
                                <small class="fw-medium">
                                    <span class="text-success">
                                        <i class="fi fi-rr-arrow-small-up scale-3x"></i> +5%
                                    </span> Last Month
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg">
                        <div class="card bg-info bg-opacity-05 shadow-none border-0">
                            <div class="card-body">
                                <div class="avatar bg-info shadow-info rounded-circle text-white mb-3">
                                    <i class="fi fi-sr-user-add"></i>
                                </div>
                                <h3>218</h3>
                                <h6 class="mb-0">New Employee</h6>
                                <small class="fw-medium">
                                    <span class="text-success">
                                        <i class="fi fi-rr-arrow-small-up scale-3x"></i> +3.2%
                                    </span> Last Month
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg">
                        <div class="card bg-secondary bg-opacity-05 shadow-none border-0">
                            <div class="card-body">
                                <div class="avatar bg-warning shadow-warning rounded-circle text-white mb-3">
                                    <i class="fi fi-sr-delete-user"></i>
                                </div>
                                <h3>126</h3>
                                <h6 class="mb-0">On Leave</h6>
                                <small class="fw-medium">
                                    <span class="text-danger">
                                        <i class="fi fi-rr-arrow-small-down scale-3x"></i> -2%
                                    </span> Last Month
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-6 col-lg">
                        <div class="card bg-success bg-opacity-05 shadow-none border-0">
                            <div class="card-body">
                                <div class="avatar bg-success shadow-success rounded-circle text-white mb-3">
                                    <i class="fi fi-sr-shopping-bag"></i>
                                </div>
                                <h3>776</h3>
                                <h6 class="mb-0">Job Applicants</h6>
                                <small class="fw-medium">
                                    <span class="text-success">
                                        <i class="fi fi-rr-arrow-small-down scale-3x"></i> +8%
                                    </span> Last Month
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg">
                        <div class="card bg-danger bg-opacity-05 shadow-none border-0">
                            <div class="card-body">
                                <div class="avatar bg-danger shadow-danger rounded-circle text-white mb-3">
                                    <i class="fi fi-sr-clock-three"></i>
                                </div>
                                <h3>1017</h3>
                                <h6 class="mb-0">Over Time</h6>
                                <small class="fw-medium">
                                    <span class="text-danger">
                                        <i class="fi fi-rr-arrow-small-down scale-3x"></i> -8%
                                    </span> Last Month
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-3">
                <div class="card overflow-hidden z-1">
                    <div class="card-body">
                        <div class="w-75">
                            <h6 class="card-title">Create Announcement</h6>
                            <p>Make a announcement to your employee</p>
                        </div>
                        <img src="{{ asset('backend/assets/images/media/svg/media1.svg') }}" alt=""
                            class="position-absolute bottom-0 end-0 z-n1">
                    </div>
                    <div class="card-footer border-0 pt-0">
                        <a href="calendar.html" class="btn btn-outline-light waves-effect btn-shadow">Create
                            Now</a>
                    </div>
                </div>
            </div>

            <div class="col-xxl-7 col-lg-6">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between border-0 pb-0">
                        <h6 class="card-title mb-0">Employee Structure</h6>
                        <button type="button" class="btn btn-sm btn-outline-light btn-shadow waves-effect">Download
                            Report</button>
                    </div>
                    <div class="card-body p-2">
                        <div id="chartEmployee"></div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-5 col-lg-6">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between border-0 pb-0">
                        <h6 class="card-title mb-0">Company Pay</h6>
                        <select class="selectpicker" data-style="btn-sm btn-outline-light btn-shadow waves-effect">
                            <option value="pending">2024</option>
                            <option>2023</option>
                            <option>2022</option>
                            <option>2021</option>
                        </select>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-sm-6">
                                <div class="maxw-250px ratio ratio-1x1">
                                    <canvas id="companyPayChart"></canvas>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="d-grid gap-2">
                                    <div class="d-flex gap-1 align-items-center mx-1">
                                        <i class="fa fa-circle text-danger text-2xs me-1"></i>
                                        <strong class="text-dark fw-semibold">15%</strong> Salary
                                    </div>
                                    <div class="d-flex gap-1 align-items-center mx-1">
                                        <i class="fa fa-circle text-success text-2xs me-1"></i>
                                        <strong class="text-dark fw-semibold">08%</strong> Bonus
                                    </div>
                                    <div class="d-flex gap-1 align-items-center mx-1">
                                        <i class="fa fa-circle text-info text-2xs me-1"></i>
                                        <strong class="text-dark fw-semibold">20%</strong> Commission
                                    </div>
                                    <div class="d-flex gap-1 align-items-center mx-1">
                                        <i class="fa fa-circle text-secondary text-2xs me-1"></i>
                                        <strong class="text-dark fw-semibold">11%</strong> Overtime
                                    </div>
                                    <div class="d-flex gap-1 align-items-center mx-1">
                                        <i class="fa fa-circle text-primary text-2xs me-1"></i>
                                        <strong class="text-dark fw-semibold">28%</strong> Reimbursement
                                    </div>
                                    <div class="d-flex gap-1 align-items-center mx-1">
                                        <i class="fa fa-circle text-warning text-2xs me-1"></i>
                                        <strong class="text-dark fw-semibold">18%</strong> Benefits
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row gy-3 align-items-center">
                            <div class="col-sm-6">
                                <p class="mb-0">2024 Download Report Company Trends and Insights</p>
                            </div>
                            <div class="col-sm-6 text-sm-end">
                                <button type="button" class="btn btn-primary waves-effect waves-light">
                                    <i class="fi fi-rr-download me-1"></i> Download Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-4 col-lg-5">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between border-0 pb-0">
                        <h6 class="card-title mb-0">Recent Job Application</h6>
                        <a href="recruitment.html" class="btn-link">View All</a>
                    </div>
                    <div class="card-body pb-3">
                        <ul class="list-group list-group-hover list-group-smooth list-group-unlined list-group-outer">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="avatar rounded-circle me-1">
                                    <img src="{{ asset('backend/assets/images/avatar/avatar1.webp') }}" alt="">
                                </div>
                                <div class="ms-2 me-auto">
                                    <h6 class="mb-0">Sophia Hall</h6>
                                    <small>Front-End Developer</small>
                                </div>
                                <div class="dropdown select-status">
                                    <button
                                        class="btn btn-sm btn-secondary btn-sm dropdown-toggle waves-effect waves-light"
                                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Select Status
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0);"
                                                data-class="btn-outline-light" data-selected="true">Pending</a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0);"
                                                data-class="btn-subtle-primary">Approved</a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0);"
                                                data-class="btn-subtle-secondary">Rejected</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="avatar rounded-circle me-1">
                                    <img src="{{ asset('backend/assets/images/avatar/avatar2.webp') }}" alt="">
                                </div>
                                <div class="ms-2 me-auto">
                                    <h6 class="mb-0">Emma Smith</h6>
                                    <small>Back-End Developer</small>
                                </div>
                                <div class="dropdown select-status">
                                    <button
                                        class="btn btn-sm btn-secondary btn-sm dropdown-toggle waves-effect waves-light"
                                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Select Status
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0);"
                                                data-class="btn-outline-light">Pending</a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0);"
                                                data-class="btn-subtle-primary" data-selected="true">Approved</a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0);"
                                                data-class="btn-subtle-secondary">Rejected</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="avatar rounded-circle me-1">
                                    <img src="{{ asset('backend/assets/images/avatar/avatar3.webp') }}" alt="">
                                </div>
                                <div class="ms-2 me-auto">
                                    <h6 class="mb-0">Olivia Clark</h6>
                                    <small>UI/UX Designer</small>
                                </div>
                                <div class="dropdown select-status">
                                    <button
                                        class="btn btn-sm btn-secondary btn-sm dropdown-toggle waves-effect waves-light"
                                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Select Status
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0);"
                                                data-class="btn-outline-light">Pending</a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0);"
                                                data-class="btn-subtle-primary">Approved</a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0);"
                                                data-class="btn-subtle-secondary" data-selected="true">Rejected</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="avatar rounded-circle me-1">
                                    <img src="{{ asset('backend/assets/images/avatar/avatar4.webp') }}" alt="">
                                </div>
                                <div class="ms-2 me-auto">
                                    <h6 class="mb-0">Ava Lewis</h6>
                                    <small>Web Designer</small>
                                </div>
                                <div class="dropdown select-status">
                                    <button
                                        class="btn btn-sm btn-secondary btn-sm dropdown-toggle waves-effect waves-light"
                                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Select Status
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0);"
                                                data-class="btn-outline-light" data-selected="true">Pending</a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0);"
                                                data-class="btn-subtle-primary">Approved</a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0);"
                                                data-class="btn-subtle-secondary">Rejected</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="avatar rounded-circle me-1">
                                    <img src="{{ asset('backend/assets/images/avatar/avatar5.webp') }}" alt="">
                                </div>
                                <div class="ms-2 me-auto">
                                    <h6 class="mb-0">Isabella Walker</h6>
                                    <small>Full-Stack Developer</small>
                                </div>
                                <div class="dropdown select-status">
                                    <button
                                        class="btn btn-sm btn-secondary btn-sm dropdown-toggle waves-effect waves-light"
                                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Select Status
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0);"
                                                data-class="btn-outline-light">Pending</a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0);"
                                                data-class="btn-subtle-primary" data-selected="true">Approved</a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0);"
                                                data-class="btn-subtle-secondary">Rejected</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-xxl-8 col-lg-7">
                <div class="card overflow-hidden">
                    <div
                        class="card-header d-flex flex-wrap gap-3 align-items-center justify-content-between border-0 pb-0">
                        <h6 class="card-title mb-0">Employee’s Leave</h6>
                        <div id="dt_EmployeeLeave_Search"></div>
                    </div>
                    <div class="card-body px-3 pt-2 pb-0 gradient-layer">
                        <table id="dt_EmployeeLeave" class="table display table-row-rounded">
                            <thead class="table-light">
                                <tr>
                                    <th class="minw-150px">Name</th>
                                    <th class="minw-200px">Department</th>
                                    <th class="minw-150px">Days</th>
                                    <th class="minw-150px">Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>William Johnson</td>
                                    <td>Back-End Developer</td>
                                    <td>2 Days</td>
                                    <td>12 July 2024</td>
                                    <td>
                                        <div class="dropdown select-status">
                                            <button
                                                class="btn btn-sm btn-secondary btn-sm dropdown-toggle waves-effect waves-light"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Select Status
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-outline-light">Pending</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-subtle-primary" data-selected="true">Approved</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-subtle-secondary">Rejected</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Benjamin Martinez</td>
                                    <td>Full-Stack Developer</td>
                                    <td>1st Half Day</td>
                                    <td>03 July 2024</td>
                                    <td>
                                        <div class="dropdown select-status">
                                            <button
                                                class="btn btn-sm btn-secondary btn-sm dropdown-toggle waves-effect waves-light"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Select Status
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-outline-light">Pending</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-subtle-primary" data-selected="true">Approved</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-subtle-secondary">Rejected</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Alexander Brown</td>
                                    <td>Mobile App Developer</td>
                                    <td>4 Days</td>
                                    <td>27 July 2024</td>
                                    <td>
                                        <div class="dropdown select-status">
                                            <button
                                                class="btn btn-sm btn-secondary btn-sm dropdown-toggle waves-effect waves-light"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Select Status
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-outline-light">Pending</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-subtle-primary" data-selected="true">Approved</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-subtle-secondary">Rejected</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Michael Davis</td>
                                    <td>UI/UX Designer</td>
                                    <td>2nd Half Day</td>
                                    <td>05 June 2024</td>
                                    <td>
                                        <div class="dropdown select-status">
                                            <button
                                                class="btn btn-sm btn-secondary btn-sm dropdown-toggle waves-effect waves-light"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Select Status
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-outline-light">Pending</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-subtle-primary" data-selected="true">Approved</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-subtle-secondary">Rejected</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>David Wilson</td>
                                    <td>DevOps Engineer</td>
                                    <td>1 Days</td>
                                    <td>04 Aug 2024</td>
                                    <td>
                                        <div class="dropdown select-status">
                                            <button
                                                class="btn btn-sm btn-secondary btn-sm dropdown-toggle waves-effect waves-light"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Select Status
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-outline-light">Pending</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-subtle-primary" data-selected="true">Approved</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-subtle-secondary">Rejected</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Brielle Williamson</td>
                                    <td>Back-End Developer</td>
                                    <td>2 Days</td>
                                    <td>12 July 2024</td>
                                    <td>
                                        <div class="dropdown select-status">
                                            <button
                                                class="btn btn-sm btn-secondary btn-sm dropdown-toggle waves-effect waves-light"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Select Status
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-outline-light">Pending</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-subtle-primary" data-selected="true">Approved</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-subtle-secondary">Rejected</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Herrod Chandler</td>
                                    <td>Full-Stack Developer</td>
                                    <td>1st Half Day</td>
                                    <td>03 July 2024</td>
                                    <td>
                                        <div class="dropdown select-status">
                                            <button
                                                class="btn btn-sm btn-secondary btn-sm dropdown-toggle waves-effect waves-light"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Select Status
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-outline-light">Pending</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-subtle-primary">Approved</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-subtle-secondary"
                                                        data-selected="true">Rejected</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Rhona Davidson</td>
                                    <td>Mobile App Developer</td>
                                    <td>4 Days</td>
                                    <td>27 July 2024</td>
                                    <td>
                                        <div class="dropdown select-status">
                                            <button
                                                class="btn btn-sm btn-secondary btn-sm dropdown-toggle waves-effect waves-light"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Select Status
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-outline-light" data-selected="true">Pending</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-subtle-primary">Approved</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-subtle-secondary">Rejected</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Colleen Hurst</td>
                                    <td>UI/UX Designer</td>
                                    <td>2nd Half Day</td>
                                    <td>27 July 2024</td>
                                    <td>
                                        <div class="dropdown select-status">
                                            <button
                                                class="btn btn-sm btn-secondary btn-sm dropdown-toggle waves-effect waves-light"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Select Status
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-outline-light">Pending</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-subtle-primary" data-selected="true">Approved</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-subtle-secondary">Rejected</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Sonya Frost</td>
                                    <td>DevOps Engineer</td>
                                    <td>1 Days</td>
                                    <td>04 Aug 2024</td>
                                    <td>
                                        <div class="dropdown select-status">
                                            <button
                                                class="btn btn-sm btn-secondary btn-sm dropdown-toggle waves-effect waves-light"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Select Status
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-outline-light" data-selected="true">Pending</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-subtle-primary">Approved</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-subtle-secondary">Rejected</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Jena Gaines</td>
                                    <td>Back-End Developer</td>
                                    <td>2 Days</td>
                                    <td>12 July 2024</td>
                                    <td>
                                        <div class="dropdown select-status">
                                            <button
                                                class="btn btn-sm btn-secondary btn-sm dropdown-toggle waves-effect waves-light"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Select Status
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-outline-light">Pending</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-subtle-primary">Approved</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-subtle-secondary"
                                                        data-selected="true">Rejected</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Quinn Flynn</td>
                                    <td>Full-Stack Developer</td>
                                    <td>1st Half Day</td>
                                    <td>03 July 2024</td>
                                    <td>
                                        <div class="dropdown select-status">
                                            <button
                                                class="btn btn-sm btn-secondary btn-sm dropdown-toggle waves-effect waves-light"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Select Status
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-outline-light">Pending</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-subtle-primary">Approved</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-subtle-secondary"
                                                        data-selected="true">Rejected</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Charde Marshall</td>
                                    <td>Mobile App Developer</td>
                                    <td>4 Days</td>
                                    <td>27 July 2024</td>
                                    <td>
                                        <div class="dropdown select-status">
                                            <button
                                                class="btn btn-sm btn-secondary btn-sm dropdown-toggle waves-effect waves-light"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Select Status
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-outline-light" data-selected="true">Pending</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-subtle-primary">Approved</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-subtle-secondary">Rejected</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Haley Kennedy</td>
                                    <td>UI/UX Designer</td>
                                    <td>2nd Half Day</td>
                                    <td>05 June 2024</td>
                                    <td>
                                        <div class="dropdown select-status">
                                            <button
                                                class="btn btn-sm btn-secondary btn-sm dropdown-toggle waves-effect waves-light"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Select Status
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-outline-light" data-selected="true">Pending</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-subtle-primary">Approved</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-subtle-secondary">Rejected</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Tatyana Fitzpatrick</td>
                                    <td>DevOps Engineer</td>
                                    <td>1 Days</td>
                                    <td>04 Aug 2024</td>
                                    <td>
                                        <div class="dropdown select-status">
                                            <button
                                                class="btn btn-sm btn-secondary btn-sm dropdown-toggle waves-effect waves-light"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Select Status
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-outline-light">Pending</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-subtle-primary">Approved</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-subtle-secondary"
                                                        data-selected="true">Rejected</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Michael Silva</td>
                                    <td>Back-End Developer</td>
                                    <td>2 Days</td>
                                    <td>12 July 2024</td>
                                    <td>
                                        <div class="dropdown select-status">
                                            <button
                                                class="btn btn-sm btn-secondary btn-sm dropdown-toggle waves-effect waves-light"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Select Status
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-outline-light" data-selected="true">Pending</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-subtle-primary">Approved</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-subtle-secondary">Rejected</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Paul Byrd</td>
                                    <td>Full-Stack Developer</td>
                                    <td>1st Half Day</td>
                                    <td>03 July 2024</td>
                                    <td>
                                        <div class="dropdown select-status">
                                            <button
                                                class="btn btn-sm btn-secondary btn-sm dropdown-toggle waves-effect waves-light"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Select Status
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-outline-light">Pending</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-subtle-primary" data-selected="true">Approved</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-subtle-secondary">Rejected</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Gloria Little</td>
                                    <td>Mobile App Developer</td>
                                    <td>4 Days</td>
                                    <td>27 July 2024</td>
                                    <td>
                                        <div class="dropdown select-status">
                                            <button
                                                class="btn btn-sm btn-secondary btn-sm dropdown-toggle waves-effect waves-light"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Select Status
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-outline-light" data-selected="true">Pending</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-subtle-primary">Approved</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-subtle-secondary">Rejected</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Bradley Greer</td>
                                    <td>UI/UX Designer</td>
                                    <td>2nd Half Day</td>
                                    <td>05 June 2024</td>
                                    <td>
                                        <div class="dropdown select-status">
                                            <button
                                                class="btn btn-sm btn-secondary btn-sm dropdown-toggle waves-effect waves-light"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Select Status
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-outline-light">Pending</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-subtle-primary">Approved</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-subtle-secondary"
                                                        data-selected="true">Rejected</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Dai Rios</td>
                                    <td>DevOps Engineer</td>
                                    <td>1 Days</td>
                                    <td>04 Aug 2024</td>
                                    <td>
                                        <div class="dropdown select-status">
                                            <button
                                                class="btn btn-sm btn-secondary btn-sm dropdown-toggle waves-effect waves-light"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Select Status
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-outline-light">Pending</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-subtle-primary" data-selected="true">Approved</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        data-class="btn-subtle-secondary">Rejected</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-xxl-3 col-md-6">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between border-0 pb-0">
                        <h6 class="card-title mb-0">Employee Type </h6>
                        <div class="btn-group">
                            <button class="btn btn-white btn-sm btn-shadow btn-icon waves-effect dropdown-toggle"
                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fi fi-rr-menu-dots"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);">Onsite</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);">Remote</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);">Hybrid</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body py-0 px-3 d-flex align-items-center justify-content-center">
                        <div class="maxw-250px ratio ratio-1x1">
                            <canvas id="employeeTypeChart"></canvas>
                        </div>
                    </div>
                    <div class="card-footer pt-0 border-0">
                        <div class="d-flex flex-wrap gap-2 justify-content-center">
                            <div class="d-flex gap-1 align-items-center mx-1">
                                <i class="fa fa-circle text-primary text-2xs"></i>
                                <strong class="text-dark fw-semibold">800</strong> Onsite
                            </div>
                            <div class="d-flex gap-1 align-items-center mx-1">
                                <i class="fa fa-circle text-secondary text-2xs"></i>
                                <strong class="text-dark fw-semibold">105</strong> Remote
                            </div>
                            <div class="d-flex gap-1 align-items-center mx-1">
                                <i class="fa fa-circle text-info text-2xs"></i>
                                <strong class="text-dark fw-semibold">301</strong> Hybrid
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-3 col-md-6">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between border-0 pb-0">
                        <h6 class="card-title mb-0">Team</h6>
                        <div class="clearfix">
                            <a href="extended-ui/team.html" class="btn-link">View All</a>
                        </div>
                    </div>
                    <div class="card-body px-3 pb-3">
                        <ul class="list-group list-group-hover list-group-smooth list-group-space-sm">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="me-auto">
                                    <h6 class="mb-0">Marketing</h6>
                                    <small class="fw-medium text-body d-block">Member <span class="text-primary">03</span>
                                    </small>
                                </div>
                                <div class="avatar-group">
                                    <div class="avatar avatar-xs rounded-circle border border-2 border-white">
                                        <img src="{{ asset('backend/assets/images/avatar/avatar1.webp') }}"
                                            alt="">
                                    </div>
                                    <div class="avatar avatar-xs rounded-circle border border-2 border-white">
                                        <img src="{{ asset('backend/assets/images/avatar/avatar2.webp') }}"
                                            alt="">
                                    </div>
                                    <div class="avatar avatar-xs rounded-circle border border-2 border-white">
                                        <img src="{{ asset('backend/assets/images/avatar/avatar3.webp') }}"
                                            alt="">
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="me-auto">
                                    <h6 class="mb-0">Development</h6>
                                    <small class="fw-medium text-body d-block">Member <span
                                            class="text-secondary">40</span>
                                    </small>
                                </div>
                                <div class="avatar-group">
                                    <div class="avatar avatar-xs rounded-circle border border-2 border-white">
                                        <img src="{{ asset('backend/assets/images/avatar/avatar4.webp') }}"
                                            alt="">
                                    </div>
                                    <div class="avatar avatar-xs rounded-circle border border-2 border-white">
                                        <img src="{{ asset('backend/assets/images/avatar/avatar5.webp') }}"
                                            alt="">
                                    </div>
                                    <div class="avatar avatar-xs rounded-circle border border-2 border-white">
                                        <img src="{{ asset('backend/assets/images/avatar/avatar1.webp') }}"
                                            alt="">
                                    </div>
                                    <div class="avatar avatar-xs rounded-circle border border-2 border-white">
                                        <img src="{{ asset('backend/assets/images/avatar/avatar2.webp') }}"
                                            alt="">
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="me-auto">
                                    <h6 class="mb-0">Designing Team</h6>
                                    <small class="fw-medium text-body d-block">Member <span class="text-suc">03</span>
                                    </small>
                                </div>
                                <div class="avatar-group">
                                    <div class="avatar avatar-xs rounded-circle border border-2 border-white">
                                        <img src="{{ asset('backend/assets/images/avatar/avatar3.webp') }}"
                                            alt="">
                                    </div>
                                    <div class="avatar avatar-xs rounded-circle border border-2 border-white">
                                        <img src="{{ asset('backend/assets/images/avatar/avatar4.webp') }}"
                                            alt="">
                                    </div>
                                    <div class="avatar avatar-xs rounded-circle border border-2 border-white">
                                        <img src="{{ asset('backend/assets/images/avatar/avatar5.webp') }}"
                                            alt="">
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="me-auto">
                                    <h6 class="mb-0">Management</h6>
                                    <small class="fw-medium text-body d-block">Member <span class="text-primary">02</span>
                                    </small>
                                </div>
                                <div class="avatar-group">
                                    <div class="avatar avatar-xs rounded-circle border border-2 border-white">
                                        <img src="{{ asset('backend/assets/images/avatar/avatar1.webp') }}"
                                            alt="">
                                    </div>
                                    <div class="avatar avatar-xs rounded-circle border border-2 border-white">
                                        <img src="{{ asset('backend/assets/images/avatar/avatar2.webp') }}"
                                            alt="">
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="me-auto">
                                    <h6 class="mb-0">Finance</h6>
                                    <small class="fw-medium text-body d-block">Member <span class="text-primary">12</span>
                                    </small>
                                </div>
                                <div class="avatar-group">
                                    <div class="avatar avatar-xs rounded-circle border border-2 border-white">
                                        <img src="{{ asset('backend/assets/images/avatar/avatar5.webp') }}"
                                            alt="">
                                    </div>
                                    <div class="avatar avatar-xs rounded-circle border border-2 border-white">
                                        <img src="{{ asset('backend/assets/images/avatar/avatar4.webp') }}"
                                            alt="">
                                    </div>
                                    <div class="avatar avatar-xs rounded-circle border border-2 border-white">
                                        <img src="{{ asset('backend/assets/images/avatar/avatar3.webp') }}"
                                            alt="">
                                    </div>
                                    <div class="avatar avatar-xs rounded-circle border border-2 border-white">
                                        <img src="{{ asset('backend/assets/images/avatar/avatar2.webp') }}"
                                            alt="">
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-xxl-6">
                <div class="card-group overflow-hidden">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between border-0 pb-0">
                            <h6 class="card-title mb-0">Schedule</h6>
                            <button type="button"
                                class="btn btn-sm btn-outline-light btn-shadow waves-effect text-primary ms-3">
                                <i class="fi fi-rr-plus text-2xs me-1"></i> Create New
                            </button>
                        </div>
                        <div class="card-body p-3">
                            <input type="text" class="form-control d-none flatpickr-inline-custom"
                                placeholder="Select Date.." id="dateTimeFlatpickr">
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body gradient-layer" style="height: 325px;" data-simplebar>
                            <div class="p-3 bg-light bg-opacity-50 mb-2 rounded">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h6 class="mb-0">Team Stand Up</h6>
                                    <div class="clearfix d-flex align-items-center">
                                        <div class="btn-group">
                                            <button
                                                class="btn btn-action-dark btn-sm btn-icon waves-effect dropdown-toggle"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fi fi-rr-menu-dots"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);">Edit</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);">Delete</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-2xs d-flex gap-1 align-items-center">
                                    <img src="{{ asset('backend/assets/images/icons/google-meet.svg') }}" alt="">
                                    <span>On Google Meet</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <span class="badge bg-white text-black fw-semibold">Marketing</span>
                                    <span class="text-primary text-2xs fw-semibold d-flex align-items-center">
                                        <i class="fi fi-rr-clock-three me-1"></i> 06:00 - 07:00
                                    </span>
                                </div>
                            </div>
                            <div class="p-3 bg-light bg-opacity-50 mb-2 rounded">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h6 class="mb-0">All Hands Meeting</h6>
                                    <div class="clearfix d-flex align-items-center">
                                        <div class="btn-group">
                                            <button
                                                class="btn btn-action-dark btn-sm btn-icon waves-effect dropdown-toggle"
                                                aria-label="Meeting options menu" type="button"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fi fi-rr-menu-dots"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);">Edit</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);">Delete</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-2xs d-flex gap-1 align-items-center">
                                    <img src="{{ asset('backend/assets/images/icons/google-meet.svg') }}" alt="">
                                    <span>On Google Meet</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <span class="badge bg-white text-black fw-semibold">Manager</span>
                                    <span class="text-primary text-2xs fw-semibold d-flex align-items-center">
                                        <i class="fi fi-rr-clock-three me-1"></i> 06:00 - 07:00
                                    </span>
                                </div>
                            </div>
                            <div class="p-3 bg-light bg-opacity-50 mb-2 rounded">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h6 class="mb-0">Sprint Planning</h6>
                                    <div class="clearfix d-flex align-items-center">
                                        <div class="btn-group">
                                            <button
                                                class="btn btn-action-dark btn-sm btn-icon waves-effect dropdown-toggle"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fi fi-rr-menu-dots"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);">Edit</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);">Delete</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-2xs d-flex gap-1 align-items-center">
                                    <img src="{{ asset('backend/assets/images/icons/google-meet.svg') }}" alt="">
                                    <span>On Google Meet</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <span class="badge bg-white text-black fw-semibold">HR</span>
                                    <span class="text-primary text-2xs fw-semibold d-flex align-items-center">
                                        <i class="fi fi-rr-clock-three me-1"></i> 06:00 - 07:00
                                    </span>
                                </div>
                            </div>
                            <div class="p-3 bg-light bg-opacity-50 mb-2 rounded">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h6 class="mb-0">Team Stand Up</h6>
                                    <div class="clearfix d-flex align-items-center">
                                        <div class="btn-group">
                                            <button
                                                class="btn btn-action-dark btn-sm btn-icon waves-effect dropdown-toggle"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fi fi-rr-menu-dots"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);">Edit</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);">Delete</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-2xs d-flex gap-1 align-items-center">
                                    <img src="{{ asset('backend/assets/images/icons/google-meet.svg') }}" alt="">
                                    <span>On Google Meet</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <span class="badge bg-white text-black fw-semibold">Marketing</span>
                                    <span class="text-primary text-2xs fw-semibold d-flex align-items-center">
                                        <i class="fi fi-rr-clock-three me-1"></i> 06:00 - 07:00
                                    </span>
                                </div>
                            </div>
                            <div class="p-3 bg-light bg-opacity-50 mb-2 rounded">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h6 class="mb-0">All Hands Meeting</h6>
                                    <div class="clearfix d-flex align-items-center">
                                        <div class="btn-group">
                                            <button
                                                class="btn btn-action-dark btn-sm btn-icon waves-effect dropdown-toggle"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fi fi-rr-menu-dots"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);">Edit</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);">Delete</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-2xs d-flex gap-1 align-items-center">
                                    <img src="{{ asset('backend/assets/images/icons/google-meet.svg') }}" alt="">
                                    <span>On Google Meet</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <span class="badge bg-white text-black fw-semibold">Manager</span>
                                    <span class="text-primary text-2xs fw-semibold d-flex align-items-center">
                                        <i class="fi fi-rr-clock-three me-1"></i> 06:00 - 07:00
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between border-0">
                        <h6 class="card-title mb-0">Employee Performance</h6>
                        <div class="dropdown">
                            <button class="btn dropdown-toggle btn-white btn-shadow waves-effect btn-sm" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                Last Month
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);">Last Month</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);">Category</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);">Published</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);">Date Modifed</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body px-2 pb-2 pt-0">
                        <table class="table table-sm table-borderless table-row-rounded mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Score</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar rounded-circle">
                                                <img src="{{ asset('backend/assets/images/avatar/avatar1.webp') }}"
                                                    alt="">
                                            </div>
                                            <div class="ms-2 me-auto">
                                                <h6 class="mb-0">Olivia Clark</h6>
                                                <small class="text-body">UI/UX Designer</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div id="employeeScore1"></div>
                                    </td>
                                    <td>
                                        <div class="btn-group float-end">
                                            <button
                                                class="btn btn-white btn-sm btn-shadow btn-icon waves-effect dropdown-toggle"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fi fi-rr-menu-dots"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);">Edit</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);">Delete</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar rounded-circle">
                                                <img src="{{ asset('backend/assets/images/avatar/avatar2.webp') }}"
                                                    alt="">
                                            </div>
                                            <div class="ms-2 me-auto">
                                                <h6 class="mb-0">Michael Davis</h6>
                                                <small class="text-body">Full-Stack Developer</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div id="employeeScore2"></div>
                                    </td>
                                    <td>
                                        <div class="btn-group float-end">
                                            <button
                                                class="btn btn-white btn-sm btn-shadow btn-icon waves-effect dropdown-toggle"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fi fi-rr-menu-dots"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);">Edit</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);">Delete</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar rounded-circle">
                                                <img src="{{ asset('backend/assets/images/avatar/avatar3.webp') }}"
                                                    alt="">
                                            </div>
                                            <div class="ms-2 me-auto">
                                                <h6 class="mb-0">Michael Davis</h6>
                                                <small class="text-body">Web Designer</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div id="employeeScore3"></div>
                                    </td>
                                    <td>
                                        <div class="btn-group float-end">
                                            <button
                                                class="btn btn-white btn-sm btn-shadow btn-icon waves-effect dropdown-toggle"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fi fi-rr-menu-dots"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);">Edit</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);">Delete</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar rounded-circle">
                                                <img src="{{ asset('backend/assets/images/avatar/avatar4.webp') }}"
                                                    alt="">
                                            </div>
                                            <div class="ms-2 me-auto">
                                                <h6 class="mb-0">David Wilson</h6>
                                                <small class="text-body">Back-End Developer</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div id="employeeScore4"></div>
                                    </td>
                                    <td>
                                        <div class="btn-group float-end">
                                            <button
                                                class="btn btn-white btn-sm btn-shadow btn-icon waves-effect dropdown-toggle"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fi fi-rr-menu-dots"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);">Edit</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);">Delete</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between border-0 pb-0">
                        <h6 class="card-title mb-0">Attendance Rate</h6>
                        <a href="javascript:void(0);"
                            class="btn btn-sm btn-outline-light waves-effect btn-shadow">Download Report</a>
                    </div>
                    <div class="card-body px-1 py-2">
                        <div id="chartAttendanceRate"></div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-7">
                <div class="card bg-gray bg-opacity-10 border-0 shadow-none">
                    <div
                        class="card-header d-flex flex-wrap gap-3 align-items-center justify-content-between border-0 pb-0">
                        <h6 class="card-title mb-0">Interview Schedule</h6>
                        <div class="clearfix d-flex align-items-center">
                            <a href="javascript:void(0);" class="btn-link me-4">View All</a>
                            <div class="dropdown">
                                <button class="btn dropdown-toggle btn-white btn-shadow waves-effect btn-sm"
                                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Last Month
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0);">Last Month</a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0);">Category</a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0);">Published</a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0);">Date Modifed</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body px-3 pb-2">
                        <div class="row gy-2">
                            <div class="col-md-6">
                                <ul class="list-group list-group-smooth list-group-unlined">
                                    <li class="list-group-item d-flex justify-content-between align-items-center mb-2">
                                        <div class="avatar rounded-circle me-1">
                                            <img src="{{ asset('backend/assets/images/avatar/avatar1.webp') }}"
                                                alt="">
                                        </div>
                                        <div class="ms-2 me-auto">
                                            <h6 class="mb-0">William Johnson</h6>
                                            <small class="text-body">Web Designer</small>
                                        </div>
                                        <span class="badge badge-lg bg-danger-subtle text-danger">12.30
                                            PM</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center mb-2">
                                        <div class="avatar rounded-circle me-1">
                                            <img src="{{ asset('backend/assets/images/avatar/avatar2.webp') }}"
                                                alt="">
                                        </div>
                                        <div class="ms-2 me-auto">
                                            <h6 class="mb-0">Alexander Brown</h6>
                                            <small class="text-body">Front-End Developer</small>
                                        </div>
                                        <span class="badge badge-lg bg-primary-subtle text-primary">24 July
                                            2024</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center mb-2">
                                        <div class="avatar rounded-circle me-1">
                                            <img src="{{ asset('backend/assets/images/avatar/avatar3.webp') }}"
                                                alt="">
                                        </div>
                                        <div class="ms-2 me-auto">
                                            <h6 class="mb-0">Michael Davis</h6>
                                            <small class="text-body">UI/UX Designer</small>
                                        </div>
                                        <span class="badge badge-lg bg-secondary-subtle text-secondary">11.00
                                            AM</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center mb-2">
                                        <div class="avatar rounded-circle me-1">
                                            <img src="{{ asset('backend/assets/images/avatar/avatar4.webp') }}"
                                                alt="">
                                        </div>
                                        <div class="ms-2 me-auto">
                                            <h6 class="mb-0">David Wilson</h6>
                                            <small class="text-body">Back-End Developer</small>
                                        </div>
                                        <span class="badge badge-lg bg-success-subtle text-success">12.30
                                            PM</span>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-group list-group-smooth list-group-unlined">
                                    <li class="list-group-item d-flex justify-content-between align-items-center mb-2">
                                        <div class="avatar rounded-circle me-1">
                                            <img src="{{ asset('backend/assets/images/avatar/avatar4.webp') }}"
                                                alt="">
                                        </div>
                                        <div class="ms-2 me-auto">
                                            <h6 class="mb-0">David Wilson</h6>
                                            <small class="text-body">Back-End Developer</small>
                                        </div>
                                        <span class="badge badge-lg bg-success-subtle text-success">12.30 -
                                            02.30</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center mb-2">
                                        <div class="avatar rounded-circle me-1">
                                            <img src="{{ asset('backend/assets/images/avatar/avatar1.webp') }}"
                                                alt="">
                                        </div>
                                        <div class="ms-2 me-auto">
                                            <h6 class="mb-0">William Johnson</h6>
                                            <small class="text-body">Web Designer</small>
                                        </div>
                                        <span class="badge badge-lg bg-secondary-subtle text-secondary">10.30
                                            AM</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center mb-2">
                                        <div class="avatar rounded-circle me-1">
                                            <img src="{{ asset('backend/assets/images/avatar/avatar2.webp') }}"
                                                alt="">
                                        </div>
                                        <div class="ms-2 me-auto">
                                            <h6 class="mb-0">Alexander Brown</h6>
                                            <small class="text-body">Front-End Developer</small>
                                        </div>
                                        <span class="badge badge-lg bg-primary-subtle text-primary">24 July
                                            2024</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center mb-2">
                                        <div class="avatar rounded-circle me-1">
                                            <img src="{{ asset('backend/assets/images/avatar/avatar3.webp') }}"
                                                alt="">
                                        </div>
                                        <div class="ms-2 me-auto">
                                            <h6 class="mb-0">Michael Davis</h6>
                                            <small class="text-body">UI/UX Designer</small>
                                        </div>
                                        <span class="badge badge-lg bg-secondary-subtle text-secondary">25 Dec
                                            2024</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-5">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between border-0 pb-0">
                        <h6 class="card-title mb-0">Task Update</h6>
                        <div class="clearfix">
                            <a href="javascript:void(0);" class="btn-link">View All</a>
                            <button type="button"
                                class="btn btn-sm btn-outline-light btn-shadow text-primary waves-effect ms-3"
                                data-bs-toggle="modal" data-bs-target="#todoTaskModal">
                                <i class="fi fi-rr-plus text-2xs me-1"></i> New Task
                            </button>
                        </div>
                    </div>
                    <div class="card-body pb-1 px-2 pt-3 overflow-auto" style="height: 325px;" data-simplebar>
                        <ul id="todoList" class="list-group list-group-smooth list-group-unlined todo-nav">
                            <li class="list-group-item d-flex gap-2 align-items-center todo-item mb-1 ps-3 pe-2 py-2">
                                <span class="sortable-handle">
                                    <svg width="16" height="17" viewBox="0 0 16 17" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M11.9998 3.16667C12.7362 3.16667 13.3332 2.56971 13.3332 1.83333C13.3332 1.09695 12.7362 0.5 11.9998 0.5C11.2635 0.5 10.6665 1.09695 10.6665 1.83333C10.6665 2.56971 11.2635 3.16667 11.9998 3.16667Z"
                                            fill="var(--bs-body-color)" />
                                        <path
                                            d="M11.9998 9.26237C12.7362 9.26237 13.3332 8.66542 13.3332 7.92904C13.3332 7.19266 12.7362 6.5957 11.9998 6.5957C11.2635 6.5957 10.6665 7.19266 10.6665 7.92904C10.6665 8.66542 11.2635 9.26237 11.9998 9.26237Z"
                                            fill="var(--bs-body-color)" />
                                        <path
                                            d="M11.9998 15.3571C12.7362 15.3571 13.3332 14.7601 13.3332 14.0238C13.3332 13.2874 12.7362 12.6904 11.9998 12.6904C11.2635 12.6904 10.6665 13.2874 10.6665 14.0238C10.6665 14.7601 11.2635 15.3571 11.9998 15.3571Z"
                                            fill="var(--bs-body-color)" />
                                        <path
                                            d="M4.7618 3.16667C5.49818 3.16667 6.09513 2.56971 6.09513 1.83333C6.09513 1.09695 5.49818 0.5 4.7618 0.5C4.02542 0.5 3.42847 1.09695 3.42847 1.83333C3.42847 2.56971 4.02542 3.16667 4.7618 3.16667Z"
                                            fill="var(--bs-body-color)" />
                                        <path
                                            d="M4.7618 9.26237C5.49818 9.26237 6.09513 8.66542 6.09513 7.92904C6.09513 7.19266 5.49818 6.5957 4.7618 6.5957C4.02542 6.5957 3.42847 7.19266 3.42847 7.92904C3.42847 8.66542 4.02542 9.26237 4.7618 9.26237Z"
                                            fill="var(--bs-body-color)" />
                                        <path
                                            d="M4.7618 15.3571C5.49818 15.3571 6.09513 14.7601 6.09513 14.0238C6.09513 13.2874 5.49818 12.6904 4.7618 12.6904C4.02542 12.6904 3.42847 13.2874 3.42847 14.0238C3.42847 14.7601 4.02542 15.3571 4.7618 15.3571Z"
                                            fill="var(--bs-body-color)" />
                                    </svg>
                                </span>
                                <input class="form-check-input todo-checkbox check-dark" type="checkbox">
                                <span class="form-label mb-0">Prepare monthly financial report</span>
                                <span class="todo-time text-body">04:25PM</span>
                                <button type="button"
                                    class="btn btn-action-gray btn-sm btn-icon waves-effect waves-light item-delete ms-auto">
                                    <i class="fi fi-rr-trash"></i>
                                </button>
                            </li>
                            <li class="list-group-item d-flex gap-2 align-items-center todo-item mb-1 ps-3 pe-2 py-2">
                                <span class="sortable-handle">
                                    <svg width="16" height="17" viewBox="0 0 16 17" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M11.9998 3.16667C12.7362 3.16667 13.3332 2.56971 13.3332 1.83333C13.3332 1.09695 12.7362 0.5 11.9998 0.5C11.2635 0.5 10.6665 1.09695 10.6665 1.83333C10.6665 2.56971 11.2635 3.16667 11.9998 3.16667Z"
                                            fill="var(--bs-body-color)" />
                                        <path
                                            d="M11.9998 9.26237C12.7362 9.26237 13.3332 8.66542 13.3332 7.92904C13.3332 7.19266 12.7362 6.5957 11.9998 6.5957C11.2635 6.5957 10.6665 7.19266 10.6665 7.92904C10.6665 8.66542 11.2635 9.26237 11.9998 9.26237Z"
                                            fill="var(--bs-body-color)" />
                                        <path
                                            d="M11.9998 15.3571C12.7362 15.3571 13.3332 14.7601 13.3332 14.0238C13.3332 13.2874 12.7362 12.6904 11.9998 12.6904C11.2635 12.6904 10.6665 13.2874 10.6665 14.0238C10.6665 14.7601 11.2635 15.3571 11.9998 15.3571Z"
                                            fill="var(--bs-body-color)" />
                                        <path
                                            d="M4.7618 3.16667C5.49818 3.16667 6.09513 2.56971 6.09513 1.83333C6.09513 1.09695 5.49818 0.5 4.7618 0.5C4.02542 0.5 3.42847 1.09695 3.42847 1.83333C3.42847 2.56971 4.02542 3.16667 4.7618 3.16667Z"
                                            fill="var(--bs-body-color)" />
                                        <path
                                            d="M4.7618 9.26237C5.49818 9.26237 6.09513 8.66542 6.09513 7.92904C6.09513 7.19266 5.49818 6.5957 4.7618 6.5957C4.02542 6.5957 3.42847 7.19266 3.42847 7.92904C3.42847 8.66542 4.02542 9.26237 4.7618 9.26237Z"
                                            fill="var(--bs-body-color)" />
                                        <path
                                            d="M4.7618 15.3571C5.49818 15.3571 6.09513 14.7601 6.09513 14.0238C6.09513 13.2874 5.49818 12.6904 4.7618 12.6904C4.02542 12.6904 3.42847 13.2874 3.42847 14.0238C3.42847 14.7601 4.02542 15.3571 4.7618 15.3571Z"
                                            fill="var(--bs-body-color)" />
                                    </svg>
                                </span>
                                <input class="form-check-input todo-checkbox check-dark" type="checkbox" checked>
                                <span class="form-label mb-0">Develop new marketing strategy</span>
                                <span class="todo-time text-body">04:25PM</span>
                                <button type="button"
                                    class="btn btn-action-gray btn-sm btn-icon waves-effect waves-light item-delete ms-auto">
                                    <i class="fi fi-rr-trash"></i>
                                </button>
                            </li>
                            <li class="list-group-item d-flex gap-2 align-items-center todo-item mb-1 ps-3 pe-2 py-2">
                                <span class="sortable-handle">
                                    <svg width="16" height="17" viewBox="0 0 16 17" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M11.9998 3.16667C12.7362 3.16667 13.3332 2.56971 13.3332 1.83333C13.3332 1.09695 12.7362 0.5 11.9998 0.5C11.2635 0.5 10.6665 1.09695 10.6665 1.83333C10.6665 2.56971 11.2635 3.16667 11.9998 3.16667Z"
                                            fill="var(--bs-body-color)" />
                                        <path
                                            d="M11.9998 9.26237C12.7362 9.26237 13.3332 8.66542 13.3332 7.92904C13.3332 7.19266 12.7362 6.5957 11.9998 6.5957C11.2635 6.5957 10.6665 7.19266 10.6665 7.92904C10.6665 8.66542 11.2635 9.26237 11.9998 9.26237Z"
                                            fill="var(--bs-body-color)" />
                                        <path
                                            d="M11.9998 15.3571C12.7362 15.3571 13.3332 14.7601 13.3332 14.0238C13.3332 13.2874 12.7362 12.6904 11.9998 12.6904C11.2635 12.6904 10.6665 13.2874 10.6665 14.0238C10.6665 14.7601 11.2635 15.3571 11.9998 15.3571Z"
                                            fill="var(--bs-body-color)" />
                                        <path
                                            d="M4.7618 3.16667C5.49818 3.16667 6.09513 2.56971 6.09513 1.83333C6.09513 1.09695 5.49818 0.5 4.7618 0.5C4.02542 0.5 3.42847 1.09695 3.42847 1.83333C3.42847 2.56971 4.02542 3.16667 4.7618 3.16667Z"
                                            fill="var(--bs-body-color)" />
                                        <path
                                            d="M4.7618 9.26237C5.49818 9.26237 6.09513 8.66542 6.09513 7.92904C6.09513 7.19266 5.49818 6.5957 4.7618 6.5957C4.02542 6.5957 3.42847 7.19266 3.42847 7.92904C3.42847 8.66542 4.02542 9.26237 4.7618 9.26237Z"
                                            fill="var(--bs-body-color)" />
                                        <path
                                            d="M4.7618 15.3571C5.49818 15.3571 6.09513 14.7601 6.09513 14.0238C6.09513 13.2874 5.49818 12.6904 4.7618 12.6904C4.02542 12.6904 3.42847 13.2874 3.42847 14.0238C3.42847 14.7601 4.02542 15.3571 4.7618 15.3571Z"
                                            fill="var(--bs-body-color)" />
                                    </svg>
                                </span>
                                <input class="form-check-input todo-checkbox check-dark" type="checkbox">
                                <span class="form-label mb-0">Reply to customer emails</span>
                                <span class="todo-time text-body">04:25PM</span>
                                <button type="button"
                                    class="btn btn-action-gray btn-sm btn-icon waves-effect waves-light item-delete ms-auto">
                                    <i class="fi fi-rr-trash"></i>
                                </button>
                            </li>
                            <li class="list-group-item d-flex gap-2 align-items-center todo-item mb-1 ps-3 pe-2 py-2">
                                <span class="sortable-handle">
                                    <svg width="16" height="17" viewBox="0 0 16 17" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M11.9998 3.16667C12.7362 3.16667 13.3332 2.56971 13.3332 1.83333C13.3332 1.09695 12.7362 0.5 11.9998 0.5C11.2635 0.5 10.6665 1.09695 10.6665 1.83333C10.6665 2.56971 11.2635 3.16667 11.9998 3.16667Z"
                                            fill="var(--bs-body-color)" />
                                        <path
                                            d="M11.9998 9.26237C12.7362 9.26237 13.3332 8.66542 13.3332 7.92904C13.3332 7.19266 12.7362 6.5957 11.9998 6.5957C11.2635 6.5957 10.6665 7.19266 10.6665 7.92904C10.6665 8.66542 11.2635 9.26237 11.9998 9.26237Z"
                                            fill="var(--bs-body-color)" />
                                        <path
                                            d="M11.9998 15.3571C12.7362 15.3571 13.3332 14.7601 13.3332 14.0238C13.3332 13.2874 12.7362 12.6904 11.9998 12.6904C11.2635 12.6904 10.6665 13.2874 10.6665 14.0238C10.6665 14.7601 11.2635 15.3571 11.9998 15.3571Z"
                                            fill="var(--bs-body-color)" />
                                        <path
                                            d="M4.7618 3.16667C5.49818 3.16667 6.09513 2.56971 6.09513 1.83333C6.09513 1.09695 5.49818 0.5 4.7618 0.5C4.02542 0.5 3.42847 1.09695 3.42847 1.83333C3.42847 2.56971 4.02542 3.16667 4.7618 3.16667Z"
                                            fill="var(--bs-body-color)" />
                                        <path
                                            d="M4.7618 9.26237C5.49818 9.26237 6.09513 8.66542 6.09513 7.92904C6.09513 7.19266 5.49818 6.5957 4.7618 6.5957C4.02542 6.5957 3.42847 7.19266 3.42847 7.92904C3.42847 8.66542 4.02542 9.26237 4.7618 9.26237Z"
                                            fill="var(--bs-body-color)" />
                                        <path
                                            d="M4.7618 15.3571C5.49818 15.3571 6.09513 14.7601 6.09513 14.0238C6.09513 13.2874 5.49818 12.6904 4.7618 12.6904C4.02542 12.6904 3.42847 13.2874 3.42847 14.0238C3.42847 14.7601 4.02542 15.3571 4.7618 15.3571Z"
                                            fill="var(--bs-body-color)" />
                                    </svg>
                                </span>
                                <input class="form-check-input todo-checkbox check-dark" type="checkbox">
                                <span class="form-label mb-0">Update website content</span>
                                <span class="todo-time text-body">04:25PM</span>
                                <button type="button"
                                    class="btn btn-action-gray btn-sm btn-icon waves-effect waves-light item-delete ms-auto">
                                    <i class="fi fi-rr-trash"></i>
                                </button>
                            </li>
                            <li class="list-group-item d-flex gap-2 align-items-center todo-item mb-1 ps-3 pe-2 py-2">
                                <span class="sortable-handle">
                                    <svg width="16" height="17" viewBox="0 0 16 17" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M11.9998 3.16667C12.7362 3.16667 13.3332 2.56971 13.3332 1.83333C13.3332 1.09695 12.7362 0.5 11.9998 0.5C11.2635 0.5 10.6665 1.09695 10.6665 1.83333C10.6665 2.56971 11.2635 3.16667 11.9998 3.16667Z"
                                            fill="var(--bs-body-color)" />
                                        <path
                                            d="M11.9998 9.26237C12.7362 9.26237 13.3332 8.66542 13.3332 7.92904C13.3332 7.19266 12.7362 6.5957 11.9998 6.5957C11.2635 6.5957 10.6665 7.19266 10.6665 7.92904C10.6665 8.66542 11.2635 9.26237 11.9998 9.26237Z"
                                            fill="var(--bs-body-color)" />
                                        <path
                                            d="M11.9998 15.3571C12.7362 15.3571 13.3332 14.7601 13.3332 14.0238C13.3332 13.2874 12.7362 12.6904 11.9998 12.6904C11.2635 12.6904 10.6665 13.2874 10.6665 14.0238C10.6665 14.7601 11.2635 15.3571 11.9998 15.3571Z"
                                            fill="var(--bs-body-color)" />
                                        <path
                                            d="M4.7618 3.16667C5.49818 3.16667 6.09513 2.56971 6.09513 1.83333C6.09513 1.09695 5.49818 0.5 4.7618 0.5C4.02542 0.5 3.42847 1.09695 3.42847 1.83333C3.42847 2.56971 4.02542 3.16667 4.7618 3.16667Z"
                                            fill="var(--bs-body-color)" />
                                        <path
                                            d="M4.7618 9.26237C5.49818 9.26237 6.09513 8.66542 6.09513 7.92904C6.09513 7.19266 5.49818 6.5957 4.7618 6.5957C4.02542 6.5957 3.42847 7.19266 3.42847 7.92904C3.42847 8.66542 4.02542 9.26237 4.7618 9.26237Z"
                                            fill="var(--bs-body-color)" />
                                        <path
                                            d="M4.7618 15.3571C5.49818 15.3571 6.09513 14.7601 6.09513 14.0238C6.09513 13.2874 5.49818 12.6904 4.7618 12.6904C4.02542 12.6904 3.42847 13.2874 3.42847 14.0238C3.42847 14.7601 4.02542 15.3571 4.7618 15.3571Z"
                                            fill="var(--bs-body-color)" />
                                    </svg>
                                </span>
                                <input class="form-check-input todo-checkbox" type="checkbox" checked>
                                <span class="form-label mb-0">Review employee performance</span>
                                <span class="todo-time text-body text-body">04:25PM</span>
                                <button type="button"
                                    class="btn btn-action-gray btn-sm btn-icon waves-effect waves-light item-delete ms-auto">
                                    <i class="fi fi-rr-trash"></i>
                                </button>
                            </li>
                            <li class="list-group-item d-flex gap-2 align-items-center todo-item mb-1 ps-3 pe-2 py-2">
                                <span class="sortable-handle">
                                    <svg width="16" height="17" viewBox="0 0 16 17" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M11.9998 3.16667C12.7362 3.16667 13.3332 2.56971 13.3332 1.83333C13.3332 1.09695 12.7362 0.5 11.9998 0.5C11.2635 0.5 10.6665 1.09695 10.6665 1.83333C10.6665 2.56971 11.2635 3.16667 11.9998 3.16667Z"
                                            fill="var(--bs-body-color)" />
                                        <path
                                            d="M11.9998 9.26237C12.7362 9.26237 13.3332 8.66542 13.3332 7.92904C13.3332 7.19266 12.7362 6.5957 11.9998 6.5957C11.2635 6.5957 10.6665 7.19266 10.6665 7.92904C10.6665 8.66542 11.2635 9.26237 11.9998 9.26237Z"
                                            fill="var(--bs-body-color)" />
                                        <path
                                            d="M11.9998 15.3571C12.7362 15.3571 13.3332 14.7601 13.3332 14.0238C13.3332 13.2874 12.7362 12.6904 11.9998 12.6904C11.2635 12.6904 10.6665 13.2874 10.6665 14.0238C10.6665 14.7601 11.2635 15.3571 11.9998 15.3571Z"
                                            fill="var(--bs-body-color)" />
                                        <path
                                            d="M4.7618 3.16667C5.49818 3.16667 6.09513 2.56971 6.09513 1.83333C6.09513 1.09695 5.49818 0.5 4.7618 0.5C4.02542 0.5 3.42847 1.09695 3.42847 1.83333C3.42847 2.56971 4.02542 3.16667 4.7618 3.16667Z"
                                            fill="var(--bs-body-color)" />
                                        <path
                                            d="M4.7618 9.26237C5.49818 9.26237 6.09513 8.66542 6.09513 7.92904C6.09513 7.19266 5.49818 6.5957 4.7618 6.5957C4.02542 6.5957 3.42847 7.19266 3.42847 7.92904C3.42847 8.66542 4.02542 9.26237 4.7618 9.26237Z"
                                            fill="var(--bs-body-color)" />
                                        <path
                                            d="M4.7618 15.3571C5.49818 15.3571 6.09513 14.7601 6.09513 14.0238C6.09513 13.2874 5.49818 12.6904 4.7618 12.6904C4.02542 12.6904 3.42847 13.2874 3.42847 14.0238C3.42847 14.7601 4.02542 15.3571 4.7618 15.3571Z"
                                            fill="var(--bs-body-color)" />
                                    </svg>
                                </span>
                                <input class="form-check-input todo-checkbox check-success" type="checkbox" checked>
                                <span class="form-label mb-0">Reply to customer emails</span>
                                <span class="todo-time text-body">04:25PM</span>
                                <button type="button"
                                    class="btn btn-action-gray btn-sm btn-icon waves-effect waves-light item-delete ms-auto">
                                    <i class="fi fi-rr-trash"></i>
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="modal fade" id="todoTaskModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header py-3">
                                    <h5 class="modal-title">Add New Task</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="taskForm">
                                        <div class="row">
                                            <div class="col-12 mb-3">
                                                <input type="text" id="todoInput" class="form-control"
                                                    placeholder="Add a new task">
                                            </div>
                                            <div class="col-12 mb-3">
                                                <select id="todoPriority" class="form-select select2" data-placeholder="--Select Priority--">
                                                    <option value="default">Default</option>
                                                    <option value="success">Completed</option>
                                                    <option value="danger">High Priority</option>
                                                    <option value="info">Info</option>
                                                </select>
                                            </div>
                                            <div class="col-12 text-end">
                                                <button type="button"
                                                    class="btn btn-light waves-effect waves-light me-2"
                                                    data-bs-dismiss="modal">Close</button>
                                                <button type="button" id="todoAdd"
                                                    class="btn btn-primary waves-effect waves-light"
                                                    data-bs-dismiss="modal">Add Task</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header py-3">
                        <h5 class="modal-title">Add Employee</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <div class="mb-3">
                                <label for="fullName" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="fullName"
                                    placeholder="Enter full name">
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email"
                                        placeholder="example@email.com">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone"
                                        placeholder="+91 9876543210">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="department" class="form-label">Department</label>
                                    <select class="form-select select2" id="department" data-placeholder="--Select Department--">
                                        <option value=""></option>
                                        <option value="HR">HR</option>
                                        <option value="Development">Development</option>
                                        <option value="Sales">Sales</option>
                                        <option value="Marketing">Marketing</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="designation" class="form-label">Designation</label>
                                    <input type="text" class="form-control" id="designation"
                                        placeholder="e.g. Software Engineer">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="joiningDate" class="form-label">Joining Date</label>
                                    <input type="date" class="form-control flatpickr-date" id="joiningDate">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label">Employment Status</label>
                                    <select class="form-select select2" id="status" data-placeholder="--Select Status--">
                                        <option value=""></option>
                                        <option value="Active">Active</option>
                                        <option value="Inactive">Inactive</option>
                                        <option value="Probation">Probation</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" rows="2" placeholder="Enter address"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="photo" class="form-label">Profile Photo</label>
                                <input class="form-control" type="file" id="photo">
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-success">Add Employee</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
