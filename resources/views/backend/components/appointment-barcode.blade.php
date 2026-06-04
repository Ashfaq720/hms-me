@php
    // Generate barcode for appointment reference number
    $appointmentBarcode = isset($appointment) && $appointment->appointment_number 
        ? generate_barcode_base64($appointment->appointment_number, 'code128', 2, 40)
        : null;
@endphp

@if($appointmentBarcode)
    <!-- Appointment Barcode Section -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-qrcode me-2"></i>Appointment Barcode
            </h5>
        </div>
        <div class="card-body text-center">
            <div class="mb-3">
                <img src="{{ $appointmentBarcode }}" alt="Appointment Barcode" style="max-width: 300px; height: auto;">
            </div>
            <div class="badge bg-secondary fs-6 p-2">
                {{ $appointment->appointment_number }}
            </div>
            <div class="mt-3">
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="printBarcode()">
                    <i class="fas fa-print me-1"></i>Print Barcode
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="downloadBarcode()">
                    <i class="fas fa-download me-1"></i>Download
                </button>
            </div>
        </div>
    </div>
@endif