<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

</head>
<body class="min-h-screen flex flex-col">

    <!-- Mobile Header -->
    <header class="lg:hidden bg-gray-800 text-white p-4 flex items-center justify-between">
        <button id="open-sidebar">
            <i class="fas fa-bars text-xl"></i>
        </button>
        <h1 class="text-xl font-bold">Admin Panel</h1>
        <div class="w-6"></div>
    </header>

    <div class="flex flex-1 overflow-hidden">
        <!-- Sidebar Desktop -->
        <aside class="fixed top-0 bottom-0 left-0 w-44 bg-gray-800 text-white flex flex-col overflow-y-auto z-50 hidden lg:flex">

            <div class="p-4 border-b border-gray-700">
                <h1 class="text-xl font-bold">Admin Panel</h1>
            </div>

            <nav class="flex-1 p-2 overflow-y-auto">
                <ul class="space-y-1">
                    <li><a href="#" class="flex items-center p-2 rounded hover:bg-gray-700"><i class="fas fa-tachometer-alt w-6 text-center"></i><span class="ml-3">Dashboard</span></a></li>
                    <li><a href="#" class="flex items-center p-2 rounded bg-gray-700"><i class="fas fa-building w-6 text-center"></i><span class="ml-3">Departemen</span></a></li>
                    <li><a href="#" class="flex items-center p-2 rounded hover:bg-gray-700"><i class="fas fa-car w-6 text-center"></i><span class="ml-3">Peran</span></a></li>
                    <li><a href="#" class="flex items-center p-2 rounded hover:bg-gray-700"><i class="fas fa-users w-6 text-center"></i><span class="ml-3">Karyawan</span></a></li>
                    <li><a href="#" class="flex items-center p-2 rounded hover:bg-gray-700"><i class="fas fa-moon w-6 text-center"></i><span class="ml-3">Hak Akses</span></a></li>
                    <li><a href="#" class="flex items-center p-2 rounded hover:bg-gray-700"><i class="fas fa-person w-6 text-center"></i><span class="ml-3">Absensi</span></a></li>
                    <li><a href="#" class="flex items-center p-2 rounded hover:bg-gray-700"><i class="fas fa-book w-6 text-center"></i><span class="ml-3">Cuti</span></a></li>
                    <li><a href="#" class="flex items-center p-2 rounded hover:bg-gray-700"><i class="fas fa-tv w-6 text-center"></i><span class="ml-3">Lembur</span></a></li>
                    <li><a href="#" class="flex items-center p-2 rounded hover:bg-gray-700"><i class="fas fa-home w-6 text-center"></i><span class="ml-3">Tugas</span></a></li>
                    <li><a href="#" class="flex items-center p-2 rounded hover:bg-gray-700"><i class="fas fa-dollar w-6 text-center"></i><span class="ml-3">Penggajian</span></a></li>
                    <li><a href="#" class="flex items-center p-2 rounded hover:bg-gray-700"><i class="fas fa-cog w-6 text-center"></i><span class="ml-3">Pengaturan</span></a></li>
                    <li><a href="#" class="flex items-center p-2 rounded hover:bg-gray-700"><i class="fas fa-list w-6 text-center"></i><span class="ml-3">Log Aktivitas</span></a></li>
                </ul>
            </nav>

            <div class="p-4 border-t border-gray-700">
                <p class="font-medium">Admin User</p>
                <p class="text-sm text-gray-200">admin@example.com</p>
            </div>
        </aside>

        <!-- Sidebar Mobile -->
        <div id="sidebar-mobile" class="fixed inset-0 w-44 bg-gray-800 text-white flex flex-col transform -translate-x-full transition-transform duration-300 z-50 lg:hidden">
            <div class="p-4 border-b border-gray-700 flex justify-between items-center">
                <h1 class="text-xl font-bold">Admin Panel</h1>
                <button id="close-sidebar"><i class="fas fa-times text-gray-300 hover:text-gray-500 hover:bg-slate-200 rounded-sm pr-1 py-1 px-1 duration-100"></i></button>
            </div>

            <nav class="flex-1 p-2 overflow-y-auto">
                <ul class="space-y-1">
                    <li><a href="#" class="flex items-center p-2 rounded hover:bg-gray-700"><i class="fas fa-tachometer-alt w-6 text-center"></i><span class="ml-3">Dashboard</span></a></li>
                    <li><a href="#" class="flex items-center p-2 rounded bg-gray-700"><i class="fas fa-building w-6 text-center"></i><span class="ml-3">Departemen</span></a></li>
                    <li><a href="#" class="flex items-center p-2 rounded hover:bg-gray-700"><i class="fas fa-users w-6 text-center"></i><span class="ml-3">Karyawan</span></a></li>
                    <li><a href="#" class="flex items-center p-2 rounded hover:bg-gray-700"><i class="fas fa-cog w-6 text-center"></i><span class="ml-3">Pengaturan</span></a></li>
                </ul>
            </nav>

            <div class="p-4 border-t border-gray-700">
                <p class="font-medium">Admin User</p>
                <p class="text-sm text-gray-200">admin@example.com</p>
            </div>
        </div>

        <!-- Main Content -->
        <main class="flex-1 lg:ml-44 overflow-y-auto bg-gray-50 min-h-screen">
            @yield('content')
        </main>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sidebarMobile = document.getElementById('sidebar-mobile');
            const openSidebarBtn = document.getElementById('open-sidebar');
            const closeSidebarBtn = document.getElementById('close-sidebar');

            openSidebarBtn.addEventListener('click', () => {
                sidebarMobile.classList.remove('-translate-x-full');
            });

            closeSidebarBtn.addEventListener('click', () => {
                sidebarMobile.classList.add('-translate-x-full');
            });
        });
    </script>

</body>
</html>
