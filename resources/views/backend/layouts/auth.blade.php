<!DOCTYPE html>
<html lang="en" data-bs-theme="light" style="color-scheme: light;">
<head>
    <meta charset="utf-8">
    <meta name="color-scheme" content="light">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Auth') - Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('backend/assets/images/favicon.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('backend/assets/images/apple-touch-icon.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">

    <!-- Required CSS -->
    <link rel="stylesheet" href="{{ asset('backend/assets/libs/flaticon/css/all/all.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/assets/libs/lucide/lucide.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/assets/libs/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/assets/libs/simplebar/simplebar.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/assets/libs/node-waves/waves.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/assets/libs/bootstrap-select/css/bootstrap-select.min.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/assets/css/styles.css') }}">
    @stack('styles')
</head>
<body>
    <div class="page-layout">
        @yield('content')
    </div>

    <!-- global JS variables (used by public JS) -->
    <script>
        window.backendAssetPath = '{{ asset('backend/assets') }}';
    </script>

    <!-- begin::GXON Page Scripts (auth layout) -->
    <script src="{{ asset('backend/assets/libs/global/global.min.js') }}"></script>
    <script src="{{ asset('backend/assets/js/appSettings.js') }}"></script>
    <script src="{{ asset('backend/assets/js/main.js') }}"></script>
    <!-- end::GXON Page Scripts -->

    @stack('scripts')
</body>
</html>
