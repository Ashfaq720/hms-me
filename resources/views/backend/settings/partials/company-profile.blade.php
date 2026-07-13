@php
    $company = company_info();
    $logo = $company['logo'] ?? setting('company_logo');
    $logoSmall = $company['logo_small'] ?? setting('company_logo_small');
@endphp

<div class="company-profile">
    <div class="d-flex align-items-center gap-3">
        @if ($logo)
            <div class="company-logo me-2">
                <img src="{{ asset($logo) }}" alt="{{ $company['name'] ?? setting('company_name') }}" style="max-height: 60px;">
            </div>
        @endif
        <div class="company-info">
            <h5 class="mb-0">{{ $company['name'] ?? setting('company_name') }}</h5>
            <p class="mb-0 small text-muted">{{ $company['address'] ?? setting('company_address') }}</p>
            <p class="mb-0 small text-muted">{{ $company['phone'] ?? setting('company_phone') }} | {{ $company['email'] ?? setting('company_email') }}</p>
            @if ($company['tax_id'] ?? setting('company_tax_id'))
                <p class="mb-0 small text-muted">Tax ID: {{ $company['tax_id'] ?? setting('company_tax_id') }}</p>
            @endif
        </div>
    </div>
</div>
