@extends('backend.layouts.master')

@section('title', 'Edit Surgery Request')

@section('content')
<div class="container-fluid">
    <div class="app-page-head d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
        <div>
            <h1 class="app-page-title mb-0">
                Edit — {{ $surgeryRequest->request_no }}
                <span class="badge {{ $surgeryRequest->status_badge_class }}">{{ $surgeryRequest->status }}</span>
            </h1>
            <div class="text-muted small">
                {{ optional($surgeryRequest->patient)->patient_name }} · {{ $surgeryRequest->encounter_type }}
            </div>
        </div>
        <a href="{{ route('ot.surgery-requests.show', $surgeryRequest->id) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    @if($surgeryRequest->status === 'Sent Back for Correction' && $surgeryRequest->rejection_reason)
        <div class="alert alert-warning">
            <strong>Correction requested:</strong> {{ $surgeryRequest->rejection_reason }}
        </div>
    @endif
    @if($surgeryRequest->status === 'Pending Information' && $surgeryRequest->pending_info_reason)
        <div class="alert alert-warning">
            <strong>Information pending:</strong> {{ $surgeryRequest->pending_info_reason }}
        </div>
    @endif

    <form action="{{ route('ot.surgery-requests.update', $surgeryRequest->id) }}" method="POST">
        @csrf @method('PUT')
        <div class="card">
            <div class="card-body">@include('ot.surgery-requests._form')</div>
            <div class="card-footer d-flex justify-content-end gap-2">
                <a href="{{ route('ot.surgery-requests.show', $surgeryRequest->id) }}" class="btn btn-light">Cancel</a>
                <button type="submit" name="save_as" value="draft" class="btn btn-secondary">
                    <i class="bi bi-save"></i> Save Changes
                </button>
                @if(in_array($surgeryRequest->status, ['Pending Information','Sent Back for Correction','Draft']))
                    <button type="submit" name="save_as" value="submit" class="btn btn-primary">
                        <i class="bi bi-send"></i> Save &amp; Resubmit
                    </button>
                @endif
            </div>
        </div>
    </form>
</div>
@endsection
