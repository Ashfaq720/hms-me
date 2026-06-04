@extends('backend.layouts.master')

@section('title', 'Health Card Management')

@section('content')
<div class="container-fluid px-4">
    {{-- Page Header --}}
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <div class="clearfix">
            <h1 class="app-page-title mb-1">Health Card Management</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="font-size: 13px;">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item">Health Card</li>
                    <li class="breadcrumb-item active">Card Management</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print Card
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#issueCardModal">
                <i class="fas fa-plus me-1"></i> Issue New Card
            </button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mt-2">
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center"
                         style="width: 52px; height: 52px; background: var(--primary-bg, #EEF2FF);">
                        <i class="fas fa-id-card" style="font-size: 22px; color: var(--primary, #4361EE);"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold">12,458</h3>
                        <p class="text-muted mb-0" style="font-size: 13px;">Total Cards</p>
                        <small class="text-success"><i class="fas fa-arrow-up"></i> 12.5%</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center"
                         style="width: 52px; height: 52px; background: var(--success-bg, #D1FAE5);">
                        <i class="fas fa-check-circle" style="font-size: 22px; color: var(--success, #10B981);"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold">11,230</h3>
                        <p class="text-muted mb-0" style="font-size: 13px;">Active Cards</p>
                        <small class="text-success"><i class="fas fa-arrow-up"></i> 8.2%</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center"
                         style="width: 52px; height: 52px; background: var(--danger-bg, #FEE2E2);">
                        <i class="fas fa-ban" style="font-size: 22px; color: var(--danger, #EF4444);"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold">1,228</h3>
                        <p class="text-muted mb-0" style="font-size: 13px;">Lost / Expired</p>
                        <small class="text-danger"><i class="fas fa-arrow-down"></i> 2.1%</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center"
                         style="width: 52px; height: 52px; background: #E0F2FE;">
                        <i class="fas fa-calendar-day" style="font-size: 22px; color: #0284C7;"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold">15</h3>
                        <p class="text-muted mb-0" style="font-size: 13px;">Issued Today</p>
                        <small class="text-success"><i class="fas fa-arrow-up"></i> 25%</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mt-3">
        <div class="card-body py-3">
            <form class="row g-2 align-items-end">
                <div class="col-lg-3 col-md-4">
                    <input type="text" class="form-control" placeholder="Search by card no, name, mobile...">
                </div>
                <div class="col-lg-2 col-md-3">
                    <select class="form-select">
                        <option selected>All Card Types</option>
                        <option>Basic</option>
                        <option>Silver</option>
                        <option>Gold</option>
                        <option>Corporate</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3">
                    <select class="form-select">
                        <option selected>All Status</option>
                        <option>Active</option>
                        <option>Inactive</option>
                        <option>Lost</option>
                        <option>Expired</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3">
                    <input type="date" class="form-control" placeholder="From Date">
                </div>
                <div class="col-lg-2 col-md-3">
                    <input type="date" class="form-control" placeholder="To Date">
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-primary">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card border-0 shadow-sm mt-3">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fw-semibold">All Health Cards</h5>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-secondary"><i class="fas fa-download me-1"></i> Export</button>
                <button class="btn btn-sm btn-outline-secondary"><i class="fas fa-sync-alt me-1"></i> Refresh</button>
            </div>
        </div>
        <div class="card-body px-0 pt-0 pb-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr style="background: #F8FAFC;">
                            <th class="ps-3" style="font-size: 12px; font-weight: 600; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Card No</th>
                            <th style="font-size: 12px; font-weight: 600; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Patient</th>
                            <th style="font-size: 12px; font-weight: 600; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Mobile</th>
                            <th style="font-size: 12px; font-weight: 600; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Card Type</th>
                            <th style="font-size: 12px; font-weight: 600; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Status</th>
                            <th style="font-size: 12px; font-weight: 600; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Points Balance</th>
                            <th style="font-size: 12px; font-weight: 600; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Issue Date</th>
                            <th style="font-size: 12px; font-weight: 600; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Expiry Date</th>
                            <th class="pe-3 text-center" style="font-size: 12px; font-weight: 600; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Row 1 --}}
                        <tr>
                            <td class="ps-3 fw-semibold" style="color: var(--primary, #4361EE);">HC-20260514-001</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white" style="width: 36px; height: 36px; background: #0D9488; font-size: 13px;">RA</div>
                                    <div>
                                        <div class="fw-semibold" style="font-size: 14px;">Rahim Ahmed</div>
                                        <small class="text-muted">PT-000245</small>
                                    </div>
                                </div>
                            </td>
                            <td>+880 1712-345678</td>
                            <td><span class="badge rounded-pill" style="background: #F59E0B; color: #fff; font-size: 11px; padding: 5px 12px;">Gold</span></td>
                            <td>
                                <span class="d-inline-flex align-items-center gap-1">
                                    <span class="rounded-circle d-inline-block" style="width: 8px; height: 8px; background: #10B981;"></span>
                                    Active
                                </span>
                            </td>
                            <td class="fw-semibold">2,450 pts</td>
                            <td>14 May 2026</td>
                            <td>14 May 2028</td>
                            <td class="pe-3 text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <button class="btn btn-sm btn-light" title="View"><i class="fas fa-eye"></i></button>
                                    <button class="btn btn-sm btn-light" title="Edit"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-light" title="Print"><i class="fas fa-print"></i></button>
                                    <button class="btn btn-sm btn-light text-danger" title="Deactivate"><i class="fas fa-power-off"></i></button>
                                </div>
                            </td>
                        </tr>
                        {{-- Row 2 --}}
                        <tr>
                            <td class="ps-3 fw-semibold" style="color: var(--primary, #4361EE);">HC-20260514-002</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white" style="width: 36px; height: 36px; background: #6366F1; font-size: 13px;">FK</div>
                                    <div>
                                        <div class="fw-semibold" style="font-size: 14px;">Fatima Khatun</div>
                                        <small class="text-muted">PT-000246</small>
                                    </div>
                                </div>
                            </td>
                            <td>+880 1815-678901</td>
                            <td><span class="badge rounded-pill" style="background: #64748B; color: #fff; font-size: 11px; padding: 5px 12px;">Basic</span></td>
                            <td>
                                <span class="d-inline-flex align-items-center gap-1">
                                    <span class="rounded-circle d-inline-block" style="width: 8px; height: 8px; background: #10B981;"></span>
                                    Active
                                </span>
                            </td>
                            <td class="fw-semibold">180 pts</td>
                            <td>14 May 2026</td>
                            <td>14 May 2027</td>
                            <td class="pe-3 text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <button class="btn btn-sm btn-light" title="View"><i class="fas fa-eye"></i></button>
                                    <button class="btn btn-sm btn-light" title="Edit"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-light" title="Print"><i class="fas fa-print"></i></button>
                                    <button class="btn btn-sm btn-light text-danger" title="Deactivate"><i class="fas fa-power-off"></i></button>
                                </div>
                            </td>
                        </tr>
                        {{-- Row 3 --}}
                        <tr>
                            <td class="ps-3 fw-semibold" style="color: var(--primary, #4361EE);">HC-20260513-015</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white" style="width: 36px; height: 36px; background: #EC4899; font-size: 13px;">MI</div>
                                    <div>
                                        <div class="fw-semibold" style="font-size: 14px;">Md. Imran Hossain</div>
                                        <small class="text-muted">PT-000244</small>
                                    </div>
                                </div>
                            </td>
                            <td>+880 1911-234567</td>
                            <td><span class="badge rounded-pill" style="background: #94A3B8; color: #fff; font-size: 11px; padding: 5px 12px;">Silver</span></td>
                            <td>
                                <span class="d-inline-flex align-items-center gap-1">
                                    <span class="rounded-circle d-inline-block" style="width: 8px; height: 8px; background: #10B981;"></span>
                                    Active
                                </span>
                            </td>
                            <td class="fw-semibold">1,120 pts</td>
                            <td>13 May 2026</td>
                            <td>13 May 2028</td>
                            <td class="pe-3 text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <button class="btn btn-sm btn-light" title="View"><i class="fas fa-eye"></i></button>
                                    <button class="btn btn-sm btn-light" title="Edit"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-light" title="Print"><i class="fas fa-print"></i></button>
                                    <button class="btn btn-sm btn-light text-danger" title="Deactivate"><i class="fas fa-power-off"></i></button>
                                </div>
                            </td>
                        </tr>
                        {{-- Row 4 --}}
                        <tr>
                            <td class="ps-3 fw-semibold" style="color: var(--primary, #4361EE);">HC-20260513-014</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white" style="width: 36px; height: 36px; background: #F97316; font-size: 13px;">NS</div>
                                    <div>
                                        <div class="fw-semibold" style="font-size: 14px;">Nasreen Sultana</div>
                                        <small class="text-muted">PT-000243</small>
                                    </div>
                                </div>
                            </td>
                            <td>+880 1612-890123</td>
                            <td><span class="badge rounded-pill" style="background: #10B981; color: #fff; font-size: 11px; padding: 5px 12px;">Corporate</span></td>
                            <td>
                                <span class="d-inline-flex align-items-center gap-1">
                                    <span class="rounded-circle d-inline-block" style="width: 8px; height: 8px; background: #10B981;"></span>
                                    Active
                                </span>
                            </td>
                            <td class="fw-semibold">3,780 pts</td>
                            <td>13 May 2026</td>
                            <td>13 May 2029</td>
                            <td class="pe-3 text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <button class="btn btn-sm btn-light" title="View"><i class="fas fa-eye"></i></button>
                                    <button class="btn btn-sm btn-light" title="Edit"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-light" title="Print"><i class="fas fa-print"></i></button>
                                    <button class="btn btn-sm btn-light text-danger" title="Deactivate"><i class="fas fa-power-off"></i></button>
                                </div>
                            </td>
                        </tr>
                        {{-- Row 5 --}}
                        <tr>
                            <td class="ps-3 fw-semibold" style="color: var(--primary, #4361EE);">HC-20260510-008</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white" style="width: 36px; height: 36px; background: #3B82F6; font-size: 13px;">KR</div>
                                    <div>
                                        <div class="fw-semibold" style="font-size: 14px;">Kamal Rahman</div>
                                        <small class="text-muted">PT-000230</small>
                                    </div>
                                </div>
                            </td>
                            <td>+880 1512-456789</td>
                            <td><span class="badge rounded-pill" style="background: #F59E0B; color: #fff; font-size: 11px; padding: 5px 12px;">Gold</span></td>
                            <td>
                                <span class="d-inline-flex align-items-center gap-1">
                                    <span class="rounded-circle d-inline-block" style="width: 8px; height: 8px; background: #64748B;"></span>
                                    Inactive
                                </span>
                            </td>
                            <td class="fw-semibold">890 pts</td>
                            <td>10 May 2026</td>
                            <td>10 May 2028</td>
                            <td class="pe-3 text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <button class="btn btn-sm btn-light" title="View"><i class="fas fa-eye"></i></button>
                                    <button class="btn btn-sm btn-light" title="Edit"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-light" title="Print"><i class="fas fa-print"></i></button>
                                    <button class="btn btn-sm btn-light text-danger" title="Deactivate"><i class="fas fa-power-off"></i></button>
                                </div>
                            </td>
                        </tr>
                        {{-- Row 6 --}}
                        <tr>
                            <td class="ps-3 fw-semibold" style="color: var(--primary, #4361EE);">HC-20260305-042</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white" style="width: 36px; height: 36px; background: #8B5CF6; font-size: 13px;">SA</div>
                                    <div>
                                        <div class="fw-semibold" style="font-size: 14px;">Shahana Akter</div>
                                        <small class="text-muted">PT-000198</small>
                                    </div>
                                </div>
                            </td>
                            <td>+880 1318-567890</td>
                            <td><span class="badge rounded-pill" style="background: #94A3B8; color: #fff; font-size: 11px; padding: 5px 12px;">Silver</span></td>
                            <td>
                                <span class="d-inline-flex align-items-center gap-1">
                                    <span class="rounded-circle d-inline-block" style="width: 8px; height: 8px; background: #F59E0B;"></span>
                                    Lost
                                </span>
                            </td>
                            <td class="fw-semibold">540 pts</td>
                            <td>05 Mar 2026</td>
                            <td>05 Mar 2028</td>
                            <td class="pe-3 text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <button class="btn btn-sm btn-light" title="View"><i class="fas fa-eye"></i></button>
                                    <button class="btn btn-sm btn-light" title="Edit"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-light" title="Print"><i class="fas fa-print"></i></button>
                                    <button class="btn btn-sm btn-light text-danger" title="Deactivate"><i class="fas fa-power-off"></i></button>
                                </div>
                            </td>
                        </tr>
                        {{-- Row 7 --}}
                        <tr>
                            <td class="ps-3 fw-semibold" style="color: var(--primary, #4361EE);">HC-20250820-119</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white" style="width: 36px; height: 36px; background: #0EA5E9; font-size: 13px;">TI</div>
                                    <div>
                                        <div class="fw-semibold" style="font-size: 14px;">Tanvir Islam</div>
                                        <small class="text-muted">PT-000152</small>
                                    </div>
                                </div>
                            </td>
                            <td>+880 1413-678901</td>
                            <td><span class="badge rounded-pill" style="background: #64748B; color: #fff; font-size: 11px; padding: 5px 12px;">Basic</span></td>
                            <td>
                                <span class="d-inline-flex align-items-center gap-1">
                                    <span class="rounded-circle d-inline-block" style="width: 8px; height: 8px; background: #EF4444;"></span>
                                    Expired
                                </span>
                            </td>
                            <td class="fw-semibold">0 pts</td>
                            <td>20 Aug 2025</td>
                            <td>20 Aug 2026</td>
                            <td class="pe-3 text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <button class="btn btn-sm btn-light" title="View"><i class="fas fa-eye"></i></button>
                                    <button class="btn btn-sm btn-light" title="Edit"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-light" title="Print"><i class="fas fa-print"></i></button>
                                    <button class="btn btn-sm btn-light text-danger" title="Deactivate"><i class="fas fa-power-off"></i></button>
                                </div>
                            </td>
                        </tr>
                        {{-- Row 8 --}}
                        <tr>
                            <td class="ps-3 fw-semibold" style="color: var(--primary, #4361EE);">HC-20260512-007</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white" style="width: 36px; height: 36px; background: #14B8A6; font-size: 13px;">AB</div>
                                    <div>
                                        <div class="fw-semibold" style="font-size: 14px;">Ayesha Begum</div>
                                        <small class="text-muted">PT-000238</small>
                                    </div>
                                </div>
                            </td>
                            <td>+880 1719-012345</td>
                            <td><span class="badge rounded-pill" style="background: #10B981; color: #fff; font-size: 11px; padding: 5px 12px;">Corporate</span></td>
                            <td>
                                <span class="d-inline-flex align-items-center gap-1">
                                    <span class="rounded-circle d-inline-block" style="width: 8px; height: 8px; background: #10B981;"></span>
                                    Active
                                </span>
                            </td>
                            <td class="fw-semibold">5,210 pts</td>
                            <td>12 May 2026</td>
                            <td>12 May 2029</td>
                            <td class="pe-3 text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <button class="btn btn-sm btn-light" title="View"><i class="fas fa-eye"></i></button>
                                    <button class="btn btn-sm btn-light" title="Edit"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-light" title="Print"><i class="fas fa-print"></i></button>
                                    <button class="btn btn-sm btn-light text-danger" title="Deactivate"><i class="fas fa-power-off"></i></button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="d-flex justify-content-between align-items-center px-3 py-3 border-top">
                <small class="text-muted">Showing 1 to 8 of 12,458 entries</small>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item disabled"><a class="page-link" href="#">&laquo;</a></li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item"><a class="page-link" href="#">4</a></li>
                        <li class="page-item"><a class="page-link" href="#">5</a></li>
                        <li class="page-item"><a class="page-link" href="#">&raquo;</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

{{-- Issue New Card Modal --}}
<div class="modal fade" id="issueCardModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Issue New Health Card</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Patient <span class="text-danger">*</span></label>
                        <select class="form-select">
                            <option selected disabled>Select Patient...</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Card Type <span class="text-danger">*</span></label>
                        <select class="form-select">
                            <option selected disabled>Select Card Type...</option>
                            <option>Basic</option>
                            <option>Silver</option>
                            <option>Gold</option>
                            <option>Corporate</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Issue Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Expiry Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary"><i class="fas fa-save me-1"></i> Issue Card</button>
            </div>
        </div>
    </div>
</div>
@endsection
