@extends('backend.layouts.master')

@section('title', 'Activity Logs')

@section('content')
    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div class="clearfix">
                <h1 class="app-page-title">Activity Logs</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Activity Logs</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-12">
                <div class="card overflow-hidden">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="card-title mb-0">All Activities</h6>
                    </div>
                    <div class="card-body p-0 pb-2">
                        <div class="table-responsive">
                            <table id="dt_basic" class="table display table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Log Name</th>
                                        <th>Description</th>
                                        <th>Causer</th>
                                        <th>Subject</th>
                                        <th>Properties</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($activities as $activity)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <span class="badge bg-info">{{ $activity->log_name }}</span>
                                            </td>
                                            <td>{{ $activity->description }}</td>
                                            <td>
                                                @if ($activity->causer)
                                                    <div class="d-flex align-items-center">
                                                        <div class="fw-medium">{{ $activity->causer->name ?? 'N/A' }}</div>
                                                    </div>
                                                @else
                                                    <span class="text-muted">System</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($activity->subject)
                                                    <span
                                                        class="badge bg-light text-dark">{{ class_basename($activity->subject_type) }}
                                                        #{{ $activity->subject_id }}</span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if ($activity->properties && $activity->properties->count() > 0)
                                                    <button type="button" class="btn btn-sm btn-light view-details-btn"
                                                        data-bs-toggle="modal" data-bs-target="#activityModal"
                                                        data-details="{{ json_encode($activity->properties) }}">
                                                        <i class="fi fi-rr-eye"></i> View
                                                    </button>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $activity->created_at->format('Y-m-d H:i:s') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="px-3 py-2">
                            {{ $activities->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Activity Details Modal -->
        <div class="modal fade" id="activityModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Activity Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <pre id="activityJson" class="bg-light p-3 rounded mb-0" style="max-height: 500px; overflow-y: auto;"></pre>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('.view-details-btn').on('click', function() {
                var details = $(this).data('details');
                $('#activityJson').text(JSON.stringify(details, null, 4));
            });
        });
    </script>
@endpush
