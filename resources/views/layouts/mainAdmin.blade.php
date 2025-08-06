<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ secure_asset('build/assets/app-MmjC4X14.css') }}">
    <script type="module" src="{{ secure_asset('build/assets/app-DFlgualb.js') }}"></script>
</head>

<body class="min-h-screen flex flex-col bg-gray-50">

    <!--Sidebar-->
    @include('components.admin.sidebar-admin')

    <!-- Main Content -->
    <main class="flex-1 lg:ml-44 overflow-y-auto bg-gray-50 min-h-screen">
        @yield('content')
    </main>

    <!--Component notifikasi-->
    @include ('components.admin.notif-succes-error')

    @stack('scripts')
</body>
</html>
