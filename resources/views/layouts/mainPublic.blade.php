<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    {{-- @vite(['resources/css/app.css', 'resources/js/app.js']) --}}
    <link rel="stylesheet" href="{{ secure_asset('build/assets/app-MmjC4X14.css') }}">
    <script type="module" src="{{ secure_asset('build/assets/app-DFlgualb.js') }}"></script>
</head>
<body class="min-h-screen bg-white">

    @yield('content')

</body>
</html>
