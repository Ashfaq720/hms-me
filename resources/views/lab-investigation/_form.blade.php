<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Name <span class="text-danger">*</span></label>
        <input type="text" id="inv_name" name="name" value="{{ old('name', $data->name ?? '') }}"
            class="form-control" required>
        @error('name')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Short Name</label>
        <input type="text" id="inv_short_name" name="short_name" value="{{ old('short_name', $data->short_name ?? '') }}"
            class="form-control" maxlength="20">
        @error('short_name')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Category</label>
        <select id="inv_category_id" name="category_id" class="form-select">
            <option value="" {{ old('category_id', $data->category_id ?? '') ? '' : 'selected' }}>Select Category</option>
            @foreach ($categories as $f)
                <option value="{{ $f->id }}"
                    {{ old('category_id', $data->category_id ?? '') == $f->id ? 'selected' : '' }}>
                    {{ $f->name }}
                </option>
            @endforeach
        </select>
        @error('category_id')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Department</label>
        <input type="text" id="inv_department" name="department" value="{{ old('department', $data->department ?? '') }}"
            class="form-control">
        @error('department')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Sample Type</label>
        <input type="text" id="inv_sample_type" name="sample_type" value="{{ old('sample_type', $data->sample_type ?? '') }}"
            class="form-control">
        @error('sample_type')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Report Time (Hours)</label>
        <input type="number" id="inv_report_time_hours" name="report_time_hours" value="{{ old('report_time_hours', $data->report_time_hours ?? '') }}"
            class="form-control">
        @error('report_time_hours')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Unit</label>
        <input type="text" id="inv_unit" name="unit" value="{{ old('unit', $data->unit ?? '') }}"
            class="form-control" maxlength="50">
        @error('unit')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Method</label>
        <input type="text" id="inv_method" name="method" value="{{ old('method', $data->method ?? '') }}"
            class="form-control">
        @error('method')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Price</label>
        <input type="number" id="inv_price" name="price" value="{{ old('price', $data->price ?? '0') }}"
            class="form-control" step="0.01" min="0">
        @error('price')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Sort Order</label>
        <input type="number" id="inv_sort_order" name="sort_order" value="{{ old('sort_order', $data->sort_order ?? '0') }}"
            class="form-control">
        @error('sort_order')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-12">
        <label class="form-label">Normal Range</label>
        <textarea id="inv_normal_range" name="normal_range" class="form-control" rows="2">{{ old('normal_range', $data->normal_range ?? '') }}</textarea>
        @error('normal_range')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-12">
        <label class="form-label">Preparation</label>
        <textarea id="inv_preparation" name="preparation" class="form-control" rows="2">{{ old('preparation', $data->preparation ?? '') }}</textarea>
        @error('preparation')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-12">
        <label class="form-label">Description</label>
        <textarea id="inv_description" name="description" class="form-control" rows="2">{{ old('description', $data->description ?? '') }}</textarea>
        @error('description')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-12">
        <label class="form-label">Notes</label>
        <textarea id="inv_notes" name="notes" class="form-control" rows="2">{{ old('notes', $data->notes ?? '') }}</textarea>
        @error('notes')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
</div>
