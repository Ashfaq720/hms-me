@extends('backend.layouts.master')

@section('title', 'Alerts — ' . $admission->icu_case_id)

@section('content')
    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div>
                <h1 class="app-page-title">Alerts</h1>
                <div class="text-muted">{{ $admission->icu_case_id }}</div>
            </div>
            <a href="{{ route('icu.admissions.show', $admission->id) }}"
                class="btn btn-outline-secondary btn-sm">Back</a>
        </div>

        @if (session('success')) <div class="alert alert-success mt-2">{{ session('success') }}</div> @endif
        @if (session('error'))   <div class="alert alert-danger mt-2">{{ session('error') }}</div>   @endif

        <div class="card mt-2">
            <div class="card-body p-2">
                <table class="table table-sm mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-2" style="width:140px;">Time</th>
                            <th style="width:130px;">Type</th>
                            <th>Message</th>
                            <th style="width:100px;">Severity</th>
                            <th style="width:100px;">Status</th>
                            <th style="width:300px;" class="text-end pe-2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($alerts as $a)
                            @php
                                $sev = match ($a->severity) {
                                    'Critical' => 'danger',
                                    'Warning'  => 'warning',
                                    default    => 'info',
                                };
                                $st = match ($a->status) {
                                    'Active'       => 'danger',
                                    'Acknowledged' => 'warning',
                                    'Closed'       => 'secondary',
                                    default        => 'dark',
                                };
                            @endphp
                            <tr>
                                <td class="ps-2"><small>{{ $a->created_at?->format('Y-m-d H:i') }}</small></td>
                                <td><small>{{ $a->alert_type }}</small></td>
                                <td>
                                    {{ $a->message }}
                                    @if ($a->action_taken)
                                        <div class="small text-muted">Action: {{ $a->action_taken }}</div>
                                    @endif
                                </td>
                                <td><span class="badge bg-{{ $sev }}">{{ $a->severity }}</span></td>
                                <td><span class="badge bg-{{ $st }}">{{ $a->status }}</span></td>
                                <td class="text-end pe-2">
                                    @if ($a->status === 'Active')
                                        <form method="POST"
                                            action="{{ route('icu.admissions.alerts.acknowledge', [$admission->id, $a->id]) }}"
                                            class="d-inline">@csrf
                                            <button class="btn btn-sm btn-outline-primary">Acknowledge</button>
                                        </form>
                                    @endif
                                    @if (in_array($a->status, ['Active', 'Acknowledged']))
                                        <button class="btn btn-sm btn-outline-success" data-bs-toggle="collapse"
                                            data-bs-target="#close-{{ $a->id }}">Close</button>
                                    @endif
                                </td>
                            </tr>
                            @if (in_array($a->status, ['Active', 'Acknowledged']))
                                <tr class="collapse" id="close-{{ $a->id }}">
                                    <td colspan="6">
                                        <form method="POST"
                                            action="{{ route('icu.admissions.alerts.close', [$admission->id, $a->id]) }}"
                                            class="row g-2">
                                            @csrf
                                            <div class="col-md-9">
                                                <input type="text" name="action_taken"
                                                    class="form-control form-control-sm"
                                                    placeholder="Action taken (required)" required>
                                            </div>
                                            <div class="col-md-3">
                                                <button class="btn btn-success btn-sm w-100">Confirm Close</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No alerts.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
