<div class="row g-3">
    <div class="col-md-12">
        <label class="form-label">Operation <span class="text-danger">*</span></label>
        <select id="operation_id" name="operation_id" class="form-select" required>
            <option value="" disabled {{ old('operation_id', $operationProcedure->operation_id ?? '') ? '' : 'selected' }}>Select
                Operation
            </option>
            @foreach ($operations as $f)
                <option value="{{ $f->id }}"
                    {{ old('operation_id', $operationProcedure->operation_id ?? '') == $f->id ? 'selected' : '' }}>
                    {{ $f->name }}</option>
            @endforeach
        </select>
        @error('operation_id')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-12">
        <label class="form-label">Name <span class="text-danger">*</span></label>
        <input type="text" id="operation_procedure_name" name="name" value="{{ old('name', $operationProcedure->name ?? '') }}"
            class="form-control" required>
        @error('name')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
</div>
