<form action="{{ route('ipd-patients.case-operations.update', [$id, $operation->id]) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row g-3">

        {{-- Operation Type --}}
        <div class="col-md-6">
            <label for="operation_type_id" class="form-label">Operation Type</label>
            <select name="operation_type_id" id="operation_type_id"
                class="form-select @error('operation_type_id') is-invalid @enderror">
                <option value="">-- Select Type --</option>
                @foreach ($operationTypes as $type)
                    <option value="{{ $type->id }}" @selected(old('operation_type_id', $operation->operation_type_id) == $type->id)>{{ $type->name }}</option>
                @endforeach
            </select>
            @error('operation_type_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Operation --}}
        <div class="col-md-6">
            <label for="operation_id" class="form-label">Operation</label>
            <select name="operation_id" id="operation_id"
                class="form-select @error('operation_id') is-invalid @enderror">
                <option value="">-- Select Operation --</option>
                @foreach ($operations as $op)
                    <option value="{{ $op->id }}" @selected(old('operation_id', $operation->operation_id) == $op->id)>{{ $op->name }}</option>
                @endforeach
            </select>
            @error('operation_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Operation Procedure --}}
        <div class="col-md-6">
            <label for="operation_procedure_id" class="form-label">Procedure</label>
            <select name="operation_procedure_id" id="operation_procedure_id"
                class="form-select @error('operation_procedure_id') is-invalid @enderror">
                <option value="">-- Select Procedure --</option>
                @foreach ($procedures as $proc)
                    <option value="{{ $proc->id }}" @selected(old('operation_procedure_id', $operation->operation_procedure_id) == $proc->id)>{{ $proc->name }}</option>
                @endforeach
            </select>
            @error('operation_procedure_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Operation Theatre --}}
        <div class="col-md-6">
            <label for="operation_theatre_id" class="form-label">Operation Theatre</label>
            <select name="operation_theatre_id" id="operation_theatre_id"
                class="form-select @error('operation_theatre_id') is-invalid @enderror">
                <option value="">-- Select Theatre --</option>
                @foreach ($theatres as $theatre)
                    <option value="{{ $theatre->id }}" @selected(old('operation_theatre_id', $operation->operation_theatre_id) == $theatre->id)>{{ $theatre->name }}</option>
                @endforeach
            </select>
            @error('operation_theatre_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Date --}}
        <div class="col-md-4">
            <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
            <input type="date" name="date" id="date"
                class="form-control @error('date') is-invalid @enderror"
                value="{{ old('date', optional($operation->date)->format('Y-m-d')) }}" required>
            @error('date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Start DateTime --}}
        <div class="col-md-4">
            <label for="start_datetime" class="form-label">Start Time</label>
            <input type="datetime-local" name="start_datetime" id="start_datetime"
                class="form-control @error('start_datetime') is-invalid @enderror"
                value="{{ old('start_datetime', optional($operation->start_datetime)->format('Y-m-d\TH:i')) }}">
            @error('start_datetime')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- End DateTime --}}
        <div class="col-md-4">
            <label for="end_datetime" class="form-label">End Time</label>
            <input type="datetime-local" name="end_datetime" id="end_datetime"
                class="form-control @error('end_datetime') is-invalid @enderror"
                value="{{ old('end_datetime', optional($operation->end_datetime)->format('Y-m-d\TH:i')) }}">
            @error('end_datetime')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Checklist Row --}}
        <div class="col-md-3">
            <div class="form-check">
                <input type="hidden" name="pre_op" value="0">
                <input class="form-check-input" type="checkbox" name="pre_op" id="pre_op" value="1"
                    @checked(old('pre_op', $operation->pre_op))>
                <label class="form-check-label" for="pre_op">Pre-Op</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check">
                <input type="hidden" name="vitals" value="0">
                <input class="form-check-input" type="checkbox" name="vitals" id="vitals" value="1"
                    @checked(old('vitals', $operation->vitals))>
                <label class="form-check-label" for="vitals">Vitals</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check">
                <input type="hidden" name="consent" value="0">
                <input class="form-check-input" type="checkbox" name="consent" id="consent" value="1"
                    @checked(old('consent', $operation->consent))>
                <label class="form-check-label" for="consent">Consent</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check">
                <input type="hidden" name="equipment" value="0">
                <input class="form-check-input" type="checkbox" name="equipment" id="equipment" value="1"
                    @checked(old('equipment', $operation->equipment))>
                <label class="form-check-label" for="equipment">Equipment</label>
            </div>
        </div>

        {{-- Diagnosis --}}
        <div class="col-md-12">
            <label for="diagnosis" class="form-label">Diagnosis</label>
            <textarea rows="2" name="diagnosis" id="diagnosis"
                class="form-control @error('diagnosis') is-invalid @enderror"
                placeholder="Enter diagnosis">{{ old('diagnosis', $operation->diagnosis) }}</textarea>
            @error('diagnosis')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Assign Doctor --}}
        <div class="col-md-6">
            <label for="assign_doctor_id" class="form-label">Assign Doctor</label>
            <select name="assign_doctor_id" id="assign_doctor_id"
                class="form-select @error('assign_doctor_id') is-invalid @enderror">
                <option value="">-- Select Doctor --</option>
                @foreach ($doctors as $doctor)
                    <option value="{{ $doctor->id }}" @selected(old('assign_doctor_id', $operation->assign_doctor_id) == $doctor->id)>{{ $doctor->name }}</option>
                @endforeach
            </select>
            @error('assign_doctor_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Assistant Doctor --}}
        <div class="col-md-6">
            <label for="assistant_doctor_id" class="form-label">Assistant Doctor</label>
            <select name="assistant_doctor_id" id="assistant_doctor_id"
                class="form-select @error('assistant_doctor_id') is-invalid @enderror">
                <option value="">-- Select Doctor --</option>
                @foreach ($doctors as $doctor)
                    <option value="{{ $doctor->id }}" @selected(old('assistant_doctor_id', $operation->assistant_doctor_id) == $doctor->id)>{{ $doctor->name }}</option>
                @endforeach
            </select>
            @error('assistant_doctor_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Main Surgeon --}}
        <div class="col-md-6">
            <label for="main_surgeon_id" class="form-label">Main Surgeon</label>
            <select name="main_surgeon_id" id="main_surgeon_id"
                class="form-select @error('main_surgeon_id') is-invalid @enderror">
                <option value="">-- Select Surgeon --</option>
                @foreach ($doctors as $doctor)
                    <option value="{{ $doctor->id }}" @selected(old('main_surgeon_id', $operation->main_surgeon_id) == $doctor->id)>{{ $doctor->name }}</option>
                @endforeach
            </select>
            @error('main_surgeon_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Anesthesiologist --}}
        <div class="col-md-6">
            <label for="anesthesiologist_id" class="form-label">Anesthesiologist</label>
            <select name="anesthesiologist_id" id="anesthesiologist_id"
                class="form-select @error('anesthesiologist_id') is-invalid @enderror">
                <option value="">-- Select Anesthesiologist --</option>
                @foreach ($doctors as $doctor)
                    <option value="{{ $doctor->id }}" @selected(old('anesthesiologist_id', $operation->anesthesiologist_id) == $doctor->id)>{{ $doctor->name }}</option>
                @endforeach
            </select>
            @error('anesthesiologist_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- OT Technician --}}
        <div class="col-md-6">
            <label for="ot_technician" class="form-label">OT Technician</label>
            <input type="text" name="ot_technician" id="ot_technician"
                class="form-control @error('ot_technician') is-invalid @enderror"
                value="{{ old('ot_technician', $operation->ot_technician) }}" placeholder="Enter OT Technician name">
            @error('ot_technician')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Status --}}
        <div class="col-md-6">
            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
            <select name="status" id="status"
                class="form-select @error('status') is-invalid @enderror" required>
                @foreach (['Scheduled', 'In Progress', 'Completed', 'Cancelled'] as $s)
                    <option value="{{ $s }}" @selected(old('status', $operation->status) == $s)>{{ $s }}</option>
                @endforeach
            </select>
            @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Remarks --}}
        <div class="col-12">
            <label for="remarks" class="form-label">Remarks</label>
            <textarea name="remarks" id="remarks" class="form-control @error('remarks') is-invalid @enderror"
                rows="2" placeholder="Enter remarks">{{ old('remarks', $operation->remarks) }}</textarea>
            @error('remarks')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Submit --}}
        <div class="col-12 d-flex justify-content-end gap-2">
            <button type="submit" class="btn btn-primary">Update</button>
        </div>

    </div>
</form>
