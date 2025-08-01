<!-- Mobile header -->
<header class="lg:hidden bg-gray-800 text-white p-4 flex items-center justify-between">
    <button id="open-sidebar">
        <i class="fas fa-bars text-xl"></i>
    </button>
    <h1 class="text-xl font-bold">Admin Panel</h1>
    <div class="w-6"></div>
</header>

<div class="flex flex-1 overflow-hidden">
    <!-- Sidebar desktop -->
    <aside class="fixed top-0 bottom-0 left-0 w-44 bg-gray-800 text-white flex flex-col overflow-y-auto z-50 hidden lg:flex">

        <div class="p-4 border-b border-gray-700">
            <h1 class="text-xl font-bold">Admin Panel</h1>
        </div>
        <nav class="flex-1 p-2 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-transparent hover:scrollbar-thumb-gray-500">
            <ul class="space-y-2 text-white">
                <li>
                    <a href="#" class="flex items-center p-2 rounded hover:bg-gray-700">
                        <i class="fas fa-tachometer-alt w-6 text-center"></i>
                        <span class="ml-3">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center p-2 rounded bg-gray-700">
                        <i class="fas fa-building w-6 text-center"></i>
                        <span class="ml-3">Departemen</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center p-2 rounded hover:bg-gray-700">
                        <i class="fas fa-user-shield w-6 text-center"></i>
                        <span class="ml-3">Jabatan</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center p-2 rounded hover:bg-gray-700">
                        <i class="fas fa-key w-6 text-center"></i>
                        <span class="ml-3">Peran & Module</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center p-2 rounded hover:bg-gray-700">
                        <i class="fas fa-users w-6 text-center"></i>
                        <span class="ml-3">Karyawan</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center p-2 rounded hover:bg-gray-700">
                        <i class="fas fa-clipboard-check w-6 text-center"></i>
                        <span class="ml-3">Absensi</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center p-2 rounded hover:bg-gray-700">
                        <i class="fas fa-business-time w-6 text-center"></i>
                        <span class="ml-3">Lembur</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center p-2 rounded hover:bg-gray-700">
                        <i class="fas fa-plane-departure w-6 text-center"></i>
                        <span class="ml-3">Cuti</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center p-2 rounded hover:bg-gray-700">
                        <i class="fas fa-tasks w-6 text-center"></i>
                        <span class="ml-3">Tugas</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center p-2 rounded hover:bg-gray-700">
                        <i class="fas fa-money-bill-wave w-6 text-center"></i>
                        <span class="ml-3">Penggajian</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center p-2 rounded hover:bg-gray-700">
                        <i class="fas fa-cog w-6 text-center"></i>
                        <span class="ml-3">Pengaturan</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center p-2 rounded hover:bg-gray-700">
                        <i class="fas fa-history w-6 text-center"></i>
                        <span class="ml-3">Log Aktivitas</span>
                    </a>
                </li>
            </ul>
        </nav>

        <div class="p-4 border-t border-gray-700">
            <p class="font-medium">Admin User</p>
            <p class="text-sm text-gray-200">admin@example.com</p>
        </div>
    </aside>

    <!-- Sidebar mobile -->
    <div id="sidebar-mobile" class="fixed inset-0 w-44 bg-gray-800 text-white flex flex-col transform -translate-x-full transition-transform duration-300 z-50 lg:hidden">
        <div class="p-4 border-b border-gray-700 flex justify-between items-center">
            <h1 class="text-xl font-bold">Admin Panel</h1>
            <button id="close-sidebar"><i class="fas fa-times text-gray-300 hover:text-gray-500 hover:bg-slate-200 rounded-sm pr-1 py-1 px-1 duration-100"></i></button>
        </div>
        <nav class="flex-1 p-2 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-transparent hover:scrollbar-thumb-gray-500">
            <ul class="space-y-2 text-white">
                <li>
                    <a href="#" class="flex items-center p-2 rounded hover:bg-gray-700">
                        <i class="fas fa-tachometer-alt w-6 text-center"></i>
                        <span class="ml-3">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center p-2 rounded bg-gray-700">
                        <i class="fas fa-building w-6 text-center"></i>
                        <span class="ml-3">Departemen</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center p-2 rounded hover:bg-gray-700">
                        <i class="fas fa-user-shield w-6 text-center"></i>
                        <span class="ml-3">Jabatan</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center p-2 rounded hover:bg-gray-700">
                        <i class="fas fa-key w-6 text-center"></i>
                        <span class="ml-3">Peran & Module</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center p-2 rounded hover:bg-gray-700">
                        <i class="fas fa-users w-6 text-center"></i>
                        <span class="ml-3">Karyawan</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center p-2 rounded hover:bg-gray-700">
                        <i class="fas fa-clipboard-check w-6 text-center"></i>
                        <span class="ml-3">Absensi</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center p-2 rounded hover:bg-gray-700">
                        <i class="fas fa-business-time w-6 text-center"></i>
                        <span class="ml-3">Lembur</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center p-2 rounded hover:bg-gray-700">
                        <i class="fas fa-plane-departure w-6 text-center"></i>
                        <span class="ml-3">Cuti</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center p-2 rounded hover:bg-gray-700">
                        <i class="fas fa-tasks w-6 text-center"></i>
                        <span class="ml-3">Tugas</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center p-2 rounded hover:bg-gray-700">
                        <i class="fas fa-money-bill-wave w-6 text-center"></i>
                        <span class="ml-3">Penggajian</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center p-2 rounded hover:bg-gray-700">
                        <i class="fas fa-cog w-6 text-center"></i>
                        <span class="ml-3">Pengaturan</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center p-2 rounded hover:bg-gray-700">
                        <i class="fas fa-history w-6 text-center"></i>
                        <span class="ml-3">Log Aktivitas</span>
                    </a>
                </li>
            </ul>
        </nav>
        <div class="p-4 border-t border-gray-700">
            <p class="font-medium">Admin User</p>
            <p class="text-sm text-gray-200">admin@example.com</p>
        </div>
    </div>
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
