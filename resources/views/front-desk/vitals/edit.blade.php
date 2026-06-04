<form action="{{ route('frontdesk.vitals.update', $vital->id) }}" method="POST" id="vitalEditForm">
    @csrf
    @method('PUT')

    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <label class="form-label">Patient ID <span class="text-danger">*</span></label>
            <input type="number" name="patient_id" class="form-control" value="{{ $vital->patient_id }}" required>
        </div>

        <div class="col-12 col-lg-6">
            <label class="form-label">Patient Name</label>
            <input type="text" name="patient_name" class="form-control" value="{{ $vital->patient_name }}">
        </div>

        <div class="col-12 col-lg-6">
            <label class="form-label">Patient Type <span class="text-danger">*</span></label>
            <select name="patient_type" class="form-select" required>
                <option value="OPD" @selected($vital->patient_type==='OPD')>OPD</option>
                <option value="Ipd" @selected($vital->patient_type==='Ipd')>Ipd</option>
                <option value="ER"  @selected($vital->patient_type==='ER')>ER</option>
            </select>
        </div>

        <div class="col-12 col-lg-6">
            <label class="form-label">Patient Token</label>
            <input type="text" name="patient_token" class="form-control" value="{{ $vital->patient_token }}">
        </div>

        <div class="col-12 col-lg-6">
            <label class="form-label">Gender</label>
            <input type="text" name="gender" class="form-control" value="{{ $vital->gender }}">
        </div>

        <div class="col-12 col-lg-6">
            <label class="form-label">Age</label>
            <input type="number" name="age" class="form-control" value="{{ $vital->age }}">
        </div>

        <div class="col-12 col-lg-6">
            <label class="form-label">Weight</label>
            <input type="number" step="0.01" name="weight" class="form-control" value="{{ $vital->weight }}">
        </div>

        <div class="col-12 col-lg-6">
            <label class="form-label">Blood Pressure</label>
            <input type="text" name="blood_pressure" class="form-control" value="{{ $vital->blood_pressure }}">
        </div>

        <div class="col-12 col-lg-6">
            <label class="form-label">Height</label>
            <input type="number" step="0.01" name="height" class="form-control" value="{{ $vital->height }}">
        </div>

        <div class="col-12 col-lg-6">
            <label class="form-label">Temperature</label>
            <input type="number" step="0.01" name="temperature" class="form-control" value="{{ $vital->temperature }}">
        </div>

        <div class="col-12 col-lg-6">
            <label class="form-label">Heart Rate</label>
            <input type="number" name="heart_rate" class="form-control" value="{{ $vital->heart_rate }}">
        </div>

        <div class="col-12 col-lg-6">
            <label class="form-label">SPO2</label>
            <input type="number" name="spo2" class="form-control" value="{{ $vital->spo2 }}">
        </div>

        <div class="col-12 col-lg-6">
            <label class="form-label">Respiratory Rate</label>
            <input type="number" name="respiratory_rate" class="form-control" value="{{ $vital->respiratory_rate }}">
        </div>

        <div class="col-12">
            <label class="form-label">Remarks</label>
            <textarea name="remarks" class="form-control" rows="3">{{ $vital->remarks }}</textarea>
        </div>

        <div class="col-12 d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Update</button>
        </div>
    </div>
</form>
