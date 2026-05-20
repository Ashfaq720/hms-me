<form action="{{ route('front_desk.vitals.store') }}" method="POST" id="vitalCreateForm">
    @csrf

    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <label class="form-label">Search Patient (Existing)</label>
            <div class="d-flex gap-2 align-items-start">
                <div class="flex-grow-1">
                    <select name="patient_id" id="patient_search" required
                        class="form-select patient-select @error('patient_id') is-invalid @enderror">
                        <option value="">-- Search by Name / Patient ID / Contact --</option>

                        @foreach ($patients as $p)
                            <option value="{{ $p->id }}" {{ old('patient_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->patient_name }} | {{ $p->mobileno }}
                            </option>
                        @endforeach
                    </select>

                    @error('patient_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

        </div>


        <div class="col-12 col-lg-6">
            <label class="form-label">Patient Type <span class="text-danger">*</span></label>
            <select name="patient_type" class="form-select" required>
                <option value="">Choose One</option>
                <option value="OPD">OPD</option>
                <option value="Ipd">Ipd</option>
                <option value="ER">ER</option>
            </select>
        </div>

        <div class="col-12 col-lg-6">
            <label class="form-label">Patient Token</label>
            <input type="text" name="patient_token" class="form-control" placeholder="Enter token Number">
        </div>

        <div class="col-12 col-lg-6">
            <label class="form-label">Gender</label>
            <select name="gender" class="form-select">
                <option value="">--</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>
        </div>

        <div class="col-12 col-lg-6">
            <label class="form-label">Age</label>
            <input type="number" name="age" class="form-control" placeholder="Enter Age">
        </div>

        <div class="col-12 col-lg-6">
            <label class="form-label">Weight</label>
            <input type="number" step="0.01" name="weight" class="form-control" placeholder="Enter weight">
        </div>

        <div class="col-12 col-lg-6">
            <label class="form-label">Blood Pressure</label>
            <input type="text" name="blood_pressure" class="form-control" placeholder="120/80">
        </div>

        <div class="col-12 col-lg-6">
            <label class="form-label">Height</label>
            <input type="number" step="0.01" name="height" class="form-control" placeholder="Enter Height">
        </div>

        <div class="col-12 col-lg-6">
            <label class="form-label">Temperature</label>
            <input type="number" step="0.01" name="temperature" class="form-control"
                placeholder="Enter Temperature">
        </div>

        <div class="col-12 col-lg-6">
            <label class="form-label">Heart Rate</label>
            <input type="number" name="heart_rate" class="form-control" placeholder="Enter heart rate">
        </div>

        <div class="col-12 col-lg-6">
            <label class="form-label">SPO2</label>
            <input type="number" name="spo2" class="form-control" placeholder="Enter Spo2">
        </div>

        <div class="col-12 col-lg-6">
            <label class="form-label">Respiratory Rate</label>
            <input type="number" name="respiratory_rate" class="form-control" placeholder="Enter respiratory">
        </div>

        <div class="col-12">
            <label class="form-label">Remarks</label>
            <textarea name="remarks" class="form-control" rows="3" placeholder="Notes..."></textarea>
        </div>

        <div class="col-12 d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save</button>
        </div>
    </div>
</form>
