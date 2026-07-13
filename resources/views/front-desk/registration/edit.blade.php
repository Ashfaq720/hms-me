<form action="{{ route('frontdesk.registration.update', $appointment->id) }}" method="POST" enctype="multipart/form-data" id="fdRegEditForm">
    @csrf
    @method('PUT')

    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <label class="form-label">Appointment Date <span class="text-danger">*</span></label>
            <input type="date" name="appointment_date" class="form-control"
                   value="{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('Y-m-d') }}" required>

            <div class="mt-3">
                <label class="form-label">Patient Type <span class="text-danger">*</span></label>
                <select name="patient_type" class="form-select" required>
                    <option value="OPD" @selected($appointment->patient_type==='OPD')>OPD</option>
                    <option value="Ipd" @selected($appointment->patient_type==='Ipd')>Ipd</option>
                    <option value="ER"  @selected($appointment->patient_type==='ER')>ER</option>
                </select>
            </div>

            <div class="mt-3">
                <label class="form-label">Registration Type <span class="text-danger">*</span></label>
                <select name="registration_type" class="form-select" required>
                    <option value="NEW_PATIENT" @selected($appointment->registration_type==='NEW_PATIENT')>New Patient</option>
                    <option value="EXISTING_PATIENT" @selected($appointment->registration_type==='EXISTING_PATIENT')>Existing Patient</option>
                    <option value="UNKNOWN" @selected($appointment->registration_type==='UNKNOWN')>Unknown / Emergency Patient</option>
                </select>
            </div>

            <div class="mt-3">
                <label class="form-label">NID/Passport/ID</label>
                <input type="text" name="nid_passport" class="form-control" value="{{ $appointment->nid_passport }}">
            </div>

            <div class="mt-3">
                <label class="form-label">Upload Document (replace)</label>
                <input type="file" name="supporting_doc" class="form-control">
                @if(!empty($appointment->supporting_doc))
                    <div class="small mt-1 text-muted">Current: {{ $appointment->supporting_doc }}</div>
                @endif
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <label class="form-label">Status <span class="text-danger">*</span></label>
            <select name="booking_status" class="form-select" required>
                <option value="PRE_BOOK" @selected($appointment->booking_status==='PRE_BOOK')>Pre Book</option>
                <option value="WALK_IN"  @selected($appointment->booking_status==='WALK_IN')>Walk In</option>
            </select>

            <div class="mt-3">
                <label class="form-label">Doctor <span class="text-danger">*</span></label>
                <select name="doctor_id" class="form-select" required>
                    <option value="">Dr. Select Doctor</option>
                    @foreach($doctors as $d)
                        <option value="{{ $d->id }}" @selected($appointment->doctor_id==$d->id)>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mt-3">
                <label class="form-label">Department <span class="text-danger">*</span></label>
                <select name="department_id" class="form-select" required>
                    <option value="">Select Department</option>
                    @foreach($departments as $dep)
                        <option value="{{ $dep->id }}" @selected($appointment->department_id==$dep->id)>{{ $dep->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mt-3">
                <label class="form-label">Discount Type</label>
                <select name="discount_type" class="form-select">
                    <option value="">--</option>
                    <option value="CORPORATE" @selected($appointment->discount_type==='CORPORATE')>Corporate</option>
                    <option value="INSURANCE" @selected($appointment->discount_type==='INSURANCE')>Insurance</option>
                    <option value="SELF" @selected($appointment->discount_type==='SELF')>Self</option>
                </select>
            </div>

            <div class="mt-3">
                <label class="form-label">Organization Name</label>
                <input type="text" name="organization_name" class="form-control" value="{{ $appointment->organization_name }}">
            </div>

            <div class="mt-3">
                <label class="form-label">Organization Id</label>
                <input type="text" name="organization_id" class="form-control" value="{{ $appointment->organization_id }}">
            </div>

            <div class="mt-3">
                <label class="form-label">Organization Api Link</label>
                <input type="text" name="organization_api_link" class="form-control" value="{{ $appointment->organization_api_link }}">
            </div>

            <div class="mt-3">
                <label class="form-label">ER Priority</label>
                <select name="er_priority" class="form-select">
                    <option value="">--</option>
                    <option value="NORMAL">Normal</option>
                    <option value="HIGH">High</option>
                    <option value="CRITICAL">Critical</option>
                </select>
            </div>
        </div>

        <div class="col-12">
            <label class="form-label">Description</label>
            <textarea name="description" rows="4" class="form-control">{{ $appointment->description }}</textarea>
        </div>

        <div class="col-12 d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Update</button>
        </div>
    </div>
</form>
