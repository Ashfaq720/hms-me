<form action="{{ route('ipd-patients.nurse-notes.store', $id) }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="row g-3">

        {{-- Title --}}
        <div class="col-md-6">
            <label for="title" class="form-label">Title</label>
            <input type="text" name="title" id="title"
                class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}"
                placeholder="Enter title">
            @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Doctor Category --}}
        <div class="col-md-6">
            <label for="doctor_category" class="form-label">Doctor Category</label>
            <select name="doctor_category" id="doctor_category"
                class="form-select @error('doctor_category') is-invalid @enderror">
                <option value="">-- Select Category --</option>
                @foreach (['Round Doctor', 'Case Doctor'] as $cat)
                    <option value="{{ $cat }}" @selected(old('doctor_category') == $cat)>{{ $cat }}</option>
                @endforeach
            </select>
            @error('doctor_category')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Shift --}}
        <div class="col-md-4">
            <label for="shift" class="form-label">Shift</label>
            <select name="shift" id="shift"
                class="form-select @error('shift') is-invalid @enderror">
                <option value="">-- Select Shift --</option>
                @foreach (['Morning', 'Evening', 'Night'] as $s)
                    <option value="{{ $s }}" @selected(old('shift') == $s)>{{ $s }}</option>
                @endforeach
            </select>
            @error('shift')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Doctor --}}
        <div class="col-md-4">
            <label for="doctor_id" class="form-label">Doctor</label>
            <select name="doctor_id" id="doctor_id"
                class="form-select @error('doctor_id') is-invalid @enderror">
                <option value="">-- Select Doctor --</option>
                @foreach ($doctors as $doctor)
                    <option value="{{ $doctor->id }}" @selected(old('doctor_id') == $doctor->id)>{{ $doctor->name }}</option>
                @endforeach
            </select>
            @error('doctor_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Priority --}}
        <div class="col-md-4">
            <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
            <select name="priority" id="priority"
                class="form-select @error('priority') is-invalid @enderror" required>
                @foreach (['Normal', 'Urgent', 'Critical'] as $p)
                    <option value="{{ $p }}" @selected(old('priority', 'Normal') == $p)>{{ $p }}</option>
                @endforeach
            </select>
            @error('priority')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Date & Time --}}
        <div class="col-md-6">
            <label for="date" class="form-label">Date & Time <span class="text-danger">*</span></label>
            <input type="datetime-local" name="date" id="date"
                class="form-control @error('date') is-invalid @enderror" value="{{ old('date', now()->format('Y-m-d\TH:i')) }}"
                placeholder="Enter date and time" required>
            @error('date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Nurse Name --}}
        <div class="col-md-6">
            <label for="nurse_name" class="form-label">Nurse Name <span class="text-danger">*</span></label>
            <input type="text" name="nurse_name" id="nurse_name"
                class="form-control @error('nurse_name') is-invalid @enderror" value="{{ old('nurse_name') }}"
                placeholder="Enter Nurse Name" required>
            @error('nurse_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Note --}}
        <div class="col-md-12">
            <label for="note" class="form-label">Note <span class="text-danger">*</span></label>
            <textarea rows="3" name="note" id="note"
                class="form-control @error('note') is-invalid @enderror" placeholder="Enter note"
                required>{{ old('note') }}</textarea>
            @error('note')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Observations --}}
        <div class="col-12">
            <label for="observations" class="form-label">Observation (Optional)</label>
            <textarea name="observations" id="observations" class="form-control @error('observations') is-invalid @enderror"
                rows="2" placeholder="Optional observation (e.g., patient's condition, response to treatment, etc.)">{{ old('observations') }}</textarea>
            @error('observations')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- File Upload --}}
        <div class="col-md-6">
            <label for="file" class="form-label">Attachment</label>
            <input type="file" name="file" id="file"
                class="form-control @error('file') is-invalid @enderror"
                accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
            @error('file')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Submit --}}
        <div class="col-12 d-flex justify-content-end gap-2">
            <button type="reset" class="btn btn-light">Reset</button>
            <button type="submit" class="btn btn-primary">Save</button>
        </div>

    </div>
</form>
