<!DOCTYPE html>
<html lang="en" data-bs-theme="light" style="color-scheme: light;">

<head>
    <!-- begin::GXON Meta Basic -->
    <meta charset="utf-8">
    <meta name="theme-color" content="#316AFF">
    <meta name="color-scheme" content="light">
    <meta name="robots" content="index, follow">
    <meta name="author" content="LayoutDrop">
    <meta name="format-detection" content="telephone=no">
    <meta name="keywords"
        content="HR Management, HR Dashboard, Admin Template, Admin Dashboard, Bootstrap Admin, HR Admin Panel, Employee Management, Human Resources Dashboard, Responsive Admin Template, Web App Dashboard, HRMS Admin, Staff Management Dashboard, Bootstrap 5 Admin, Modern Admin Template, Admin UI Kit, ThemeForest Admin Template, SaaS Dashboard, Project Management Admin, HR Web Application, RTL Dashboard">
    <meta name="description"
        content="GXON is a professional and modern HR Management Admin Dashboard Template built with Bootstrap. It includes light and dark modes, and is ideal for managing employees, attendance, payroll, recruitment, and more — perfect for HR software and admin panels.">
    <!-- end::GXON Meta Basic -->

    <!-- begin::GXON Meta Social -->
    <meta property="og:url" content="https://gxon.layoutdrop.com/demo/">
    <meta property="og:site_name" content="GXON HR Management Admin Dashboard Template + RTL">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="en_US">
    <meta property="og:title" content="GXON HR Management Admin Dashboard Template + RTL">
    <meta property="og:description"
        content="GXON is a professional and modern HR Management Admin Dashboard Template built with Bootstrap. It includes light and dark modes, and is ideal for managing employees, attendance, payroll, recruitment, and more — perfect for HR software and admin panels.">
    <meta property="og:image" content="https://gxon.layoutdrop.com/demo/preview.png">
    <!-- end::GXON Meta Social -->

    <!-- begin::GXON Meta Twitter -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:url" content="https://gxon.layoutdrop.com/demo/">
    <meta name="twitter:creator" content="@layoutdrop">
    <meta name="twitter:title" content="GXON HR Management Admin Dashboard Template + RTL">
    <meta name="twitter:description"
        content="GXON is a professional and modern HR Management Admin Dashboard Template built with Bootstrap. It includes light and dark modes, and is ideal for managing employees, attendance, payroll, recruitment, and more — perfect for HR software and admin panels.">
    <!-- end::GXON Meta Twitter -->

    <!-- begin::GXON Website Page Title -->
    <title>
        @yield('title', 'Admin Dashboard Template + RTL')
    </title>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- end::GXON Website Page Title -->

    <!-- begin::GXON Mobile Specific -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- end::GXON Mobile Specific -->

    <!-- begin::GXON Favicon Tags -->
    <link rel="icon" type="image/png" href="{{ asset('backend/assets/images/favicon.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('backend/assets/images/apple-touch-icon.png') }}">
    <!-- end::GXON Favicon Tags -->

    <!-- begin::GXON Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap"
        rel="stylesheet">
    <!-- end::GXON Google Fonts -->

    <!-- begin::GXON Required Stylesheet -->
    <link rel="stylesheet" href="{{ asset('backend/assets/libs/flaticon/css/all/all.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/assets/libs/lucide/lucide.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/assets/libs/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/assets/libs/simplebar/simplebar.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/assets/libs/node-waves/waves.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/assets/libs/bootstrap-select/css/bootstrap-select.min.css') }}">
    <!-- end::GXON Required Stylesheet -->

    <!-- begin::GXON CSS Stylesheet -->
    <link rel="stylesheet" href="{{ asset('backend/assets/libs/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/assets/libs/datatables/datatables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/assets/css/styles.css') }}">
    <!-- end::GXON CSS Stylesheet -->
    <link rel="stylesheet" href="{{ asset('backend/assets/libs/select2/css/select2.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('backend/assets/libs/select2/css/select2-bootstrap-5-theme.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('backend/assets/libs/toastr/toastr.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('backend/assets/css/custom-theme.css') }}" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    @stack('styles')

    <script>
        // Make backend asset path available for static scripts
        window.backendAssetPath = "{{ asset('backend/assets') }}";
    </script>
</head>

<body>
    <div class="page-layout">

        @include('backend.layouts.partials.navbar')

        @include('backend.layouts.partials.common-modal')

        @include('backend.layouts.partials.sidebar')
        {{-- @include('backend.layouts.partials.sidebar-right') --}}

        <main class="app-wrapper">

            @yield('content')

        </main>

        @include('backend.layouts.partials.footer')

    </div>

    <!-- begin::GXON Page Scripts -->
    <script src="{{ asset('backend/assets/libs/global/global.min.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/sortable/Sortable.min.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/chartjs/chart.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('backend/assets/js/datatable.js') }}"></script>
    <script src="{{ asset('backend/assets/js/dashboard.js') }}"></script>
    <script src="{{ asset('backend/assets/js/todolist.js') }}"></script>
    <script src="{{ asset('backend/assets/js/appSettings.js') }}"></script>
    <script src="{{ asset('backend/assets/js/main.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/toastr/toastr.min.js') }}"></script>
    <!-- end::GXON Page Scripts -->

    <script>
        @if (session('toast_success'))
            toastr.success("{{ session('toast_success') }}");
        @endif
        @if (session('toast_error'))
            toastr.error("{{ session('toast_error') }}");
        @endif
        @if (session('success'))
            toastr.success("{{ session('success') }}");
        @endif
    </script>
    <script>
        // Ajax Popup Modal (jQuery)
        $(document).on('click', '[data-ajax-popup="true"]', function(e) {
            e.preventDefault();

            const url = $(this).data('url');
            const title = $(this).data('title') || 'Modal';
            const size = $(this).data('size') || 'lg';

            // set title
            $('#commonModalTitle').text(title);

            // handle size
            const $dialog = $('#commonModal .modal-dialog');
            $dialog.removeClass('modal-sm modal-lg modal-xl');
            if (size === 'sm') $dialog.addClass('modal-sm');
            if (size === 'lg') $dialog.addClass('modal-lg');
            if (size === 'xl') $dialog.addClass('modal-xl');

            // show loading
            $('#commonModalBody').html('<div class="text-center py-4">Loading...</div>');

            // open modal (Bootstrap 5)
            const modalEl = document.getElementById('commonModal');
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();

            // load content
            $.ajax({
                url: url,
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(html) {
                    $('#commonModalBody').html(html);

                    // Re-init all .select2 elements in the loaded content.
                    // initSelect2 (main.js) reads data-ajax-url for AJAX-powered selects,
                    // sets dropdownParent when inside a modal, and skips already-initialised elements.
                    if (typeof initSelect2 !== 'undefined') {
                        initSelect2(document.getElementById('commonModalBody'));
                    }

                    // re-init tooltips inside modal
                    $('#commonModalBody').find('[data-bs-toggle="tooltip"]').each(function() {
                        new bootstrap.Tooltip(this);
                    });
                },
                error: function(xhr) {
                    $('#commonModalBody').html(
                        '<div class="alert alert-danger mb-0">Could not load content. Please try again.</div>'
                    );
                }
            });
        });

        // Tooltip init (whole page)
        $(function() {
            $('[data-bs-toggle="tooltip"]').each(function() {
                new bootstrap.Tooltip(this);
            });
        });
    </script>

    @stack('scripts')

</body>

</html>
