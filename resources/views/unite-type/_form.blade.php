<form action="{{ $action }}" method="POST" enctype="multipart/form-data" id="uniteTypeForm">
    @csrf

    @if(isset($method) && strtoupper($method) !== 'POST')
        @method($method)
    @endif

    <div class="row g-3">
        <div class="col-md-12">
            <label class="form-label">Name <span class="text-danger">*</span></label>
            <input type="text"
                   name="name"
                   value="{{ old('name', $uniteType->name ?? '') }}"
                   class="form-control"
                   required>

            @error('name')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">
                {{ $buttonText ?? 'Save' }}
            </button>
        </div>
    </div>
</form>
