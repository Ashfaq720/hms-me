<!-- begin::GXON Footer -->
<footer class="footer-wrapper bg-body">
    <div class="container-fluid">
        <div class="row g-2 align-items-center">
            <div class="col-lg-5 col-md-6 text-center text-md-start">
                <p class="mb-0">
                    &copy; <span class="currentYear">{{ date('Y') }}</span>
                    <strong>{{ setting('company_name') ?? 'MediCraft' }}</strong>.
                    Proudly powered by <a href="javascript:void(0);">CraftCode</a>.
                </p>
            </div>
            <div class="col-lg-3 col-md-6 text-center">
                <span class="footer-status" title="All systems operational">
                    System Online
                </span>
                <span class="text-muted small ms-2 d-none d-md-inline">
                    {{ app()->environment() === 'production' ? 'v1.0' : 'dev' }}
                </span>
            </div>
            <div class="col-lg-4 col-md-12">
                <ul class="d-flex list-inline mb-0 gap-3 flex-wrap justify-content-center justify-content-lg-end">
                    <li><a href="{{ route('dashboard') }}"><i class="bi bi-house-door me-1"></i>Home</a></li>
                    @if(\Illuminate\Support\Facades\Route::has('ot.dashboard'))
                        <li><a href="{{ route('ot.dashboard') }}"><i class="bi bi-scissors me-1"></i>OT</a></li>
                    @endif
                    <li><a href="javascript:void(0);" data-bs-toggle="tooltip" title="Coming soon"><i class="bi bi-question-circle me-1"></i>Help</a></li>
                    <li><a href="javascript:void(0);" data-bs-toggle="tooltip" title="Coming soon"><i class="bi bi-life-preserver me-1"></i>Support</a></li>
                </ul>
            </div>
        </div>
    </div>
</footer>
<!-- end::GXON Footer -->
