@extends('backend.layouts.master')
@section('title','OT Documents')
@section('content')
<div class="container-fluid">
    <h1 class="app-page-title mb-3">Documents &amp; Consent</h1>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="row g-3">
        <div class="col-md-4"><div class="card">
            <div class="card-header"><strong>Upload</strong></div>
            <form action="{{ route('ot.documents.store') }}" method="POST" enctype="multipart/form-data" class="card-body">@csrf
                <div class="mb-2"><label class="form-label">Surgery Request ID</label><input name="surgery_request_id" class="form-control"></div>
                <div class="mb-2"><label class="form-label">Surgery Schedule ID</label><input name="surgery_schedule_id" class="form-control"></div>
                <div class="mb-2"><label class="form-label">Document Type *</label>
                    <select name="document_type" class="form-select" required>
                        @foreach(['consent','pre_op','intra_op','post_op','discharge','other'] as $t)<option value="{{ $t }}">{{ ucwords(str_replace('_',' ',$t)) }}</option>@endforeach
                    </select>
                </div>
                <div class="mb-2"><label class="form-label">Title *</label><input name="title" class="form-control" required></div>
                <div class="mb-2"><label class="form-label">File *</label><input type="file" name="file" class="form-control" required></div>
                <div class="mb-2"><label class="form-label">Notes</label><textarea name="notes" class="form-control"></textarea></div>
                <button class="btn btn-primary w-100">Upload</button>
            </form>
        </div></div>

        <div class="col-md-8"><div class="card">
            <div class="card-header"><strong>Documents</strong></div>
            <div class="table-responsive"><table class="table mb-0">
                <thead class="table-light"><tr><th>Title</th><th>Type</th><th>When</th><th>Signed</th><th></th></tr></thead>
                <tbody>
                    @forelse($documents as $d)
                        <tr>
                            <td>{{ $d->title }}</td>
                            <td>{{ $d->document_type }}</td>
                            <td>{{ $d->created_at?->format('Y-m-d H:i') }}</td>
                            <td>{{ $d->is_signed ? 'Yes' : 'No' }}</td>
                            <td class="text-end">
                                <a href="{{ route('ot.documents.download', $d->id) }}" class="btn btn-sm btn-outline-primary">Download</a>
                                @if(! $d->is_signed)
                                    <form action="{{ route('ot.documents.sign', $d->id) }}" method="POST" class="d-inline">@csrf
                                        <button class="btn btn-sm btn-outline-success">Sign</button>
                                    </form>
                                @endif
                                <form action="{{ route('ot.documents.destroy', $d->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">×</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">No documents.</td></tr>
                    @endforelse
                </tbody>
            </table></div>
        </div></div>
    </div>
    <div class="mt-3">{{ $documents->links() }}</div>
</div>
@endsection
