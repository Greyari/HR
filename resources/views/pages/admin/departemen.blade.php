@extends('layouts.mainAdmin')

@section('content')
    <!-- section Utama -->
    <div class="bg-gray-50 px-10 py-8">
        <div class="mx-auto">

            <!-- Judul Halaman dan Deskripsi -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 mb-8">
                <div>
                    <h1 class="text-3xl md:text-4xl font-bold text-gray-800">Manajemen Departemen</h1>
                    <p class="text-lg text-gray-600 mt-2">Kelola struktur organisasi perusahaan Anda</p>
                </div>
            </div>

            <!-- Kartu Manajemen Departemen -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">

                <!-- Header Kartu: Pencarian & Tambah -->
                <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div class="flex-1">
                        <h2 class="text-xl font-semibold text-gray-800">Daftar Departemen</h2>
                        <p class="text-gray-500 mt-1">Total {{ count($departemen) }} departemen terdaftar</p>
                    </div>
                    {{-- tes notif --}}
                    @if(session('success'))
                        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition
                            class="mb-4 p-3 bg-green-100 border border-green-300 text-green-800 rounded-md text-sm relative">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                        <!-- Input Pencarian -->
                        <div class="relative flex-1 md:w-64">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <input type="text" class="block w-full pl-10 pr-3 py-2.5 border border-gray-200 rounded-full bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Cari departemen...">
                        </div>

                        <!-- Tombol Tambah -->
                        @include('components.admin.modal-tambah-departemen')
                    </div>
                </div>

                <!-- Tabel Departemen -->
                <div class="p-6">
                    <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-800">
                                <tr>
                                    <th class="px-2 py-4 text-center text-xs font-medium text-gray-100 uppercase tracking-wide">No</th>
                                    <th class="px-4 py-4 text-center text-xs font-medium text-gray-100 uppercase tracking-wide">Nama Departemen</th>
                                    <th class="px-4 py-4 text-center text-xs font-medium text-gray-100 uppercase tracking-wide">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @forelse($departemen as $index => $item)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-2 py-3 text-center">{{ $index + 1 }}</td>
                                        <td class="px-4 py-3 text-left">{{ $item->nama_departemen }}</td>
                                        <td class="px-4 py-3">
                                            <div class="flex justify-center gap-3">
                                                <!-- Tombol Edit -->
                                                <div class="relative group inline-block">
                                                    <button class="p-2 rounded-full bg-yellow-500 text-white hover:bg-yellow-600 transition-all shadow-md">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                                        </svg>
                                                    </button>
                                                    <div class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 px-2 py-1 text-sm text-white bg-yellow-600 rounded shadow opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none">
                                                        Edit
                                                    </div>
                                                </div>

                                                <!-- Tombol Hapus -->
                                                <div class="relative group inline-block">
                                                    <form action="{{ route('departemen.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus departemen ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="p-2 rounded-full bg-red-500 text-white hover:bg-red-600 transition-all shadow-md">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                            </svg>
                                                        </button>
                                                        <div class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 px-2 py-1 text-sm text-white bg-red-600 rounded shadow opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none">
                                                            Hapus
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-gray-500">Belum ada departemen.</td>
                                    </tr>
                                @endforelse
                            </tbody>

                        </table>
                    </div>
                </div>

                <!-- Navigasi Tabel -->
                <div class="px-6 py-4 border-t border-gray-100 flex flex-col md:flex-row items-center justify-between">
                    <div class="text-sm text-gray-500 mb-4 md:mb-0">
                        Menampilkan <span class="font-semibold text-gray-800">1</span> hingga <span class="font-semibold text-gray-800">3</span> dari <span class="font-semibold text-gray-800">3</span> hasil
                    </div>

                    <nav class="flex items-center gap-1">
                        <!-- Tombol Sebelumnya -->
                        <div class="relative group inline-block">
                            <button class="px-3 py-2 rounded-full bg-white text-gray-500 border hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>

                            <!-- Tooltip -->
                            <div class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 px-2 py-1 text-sm text-white bg-gray-500 rounded shadow opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none">
                                Sebelumnya
                            </div>
                        </div>

                        <!-- Nomor Halaman -->
                        <button class="w-9 h-9 flex items-center justify-center rounded-full bg-blue-600 text-white font-medium hover:bg-blue-700 transition">
                            1
                        </button>
                        <button class="w-9 h-9 flex items-center justify-center rounded-full bg-white text-gray-700 border hover:bg-gray-100">
                            2
                        </button>
                        <button class="w-9 h-9 flex items-center justify-center rounded-full bg-white text-gray-700 border hover:bg-gray-100">
                            3
                        </button>

                        <!-- Tombol Berikutnya -->
                        <div class="relative group inline-block">
                            <button class="px-3 py-2 rounded-full bg-white text-gray-500 border hover:bg-gray-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>

                            <!-- Tooltip -->
                            <div class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 px-2 py-1 text-sm text-white bg-gray-500 rounded shadow opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none">
                                Selanjutnya
                            </div>
                        </div>
                    </nav>
                </div>

            </div>
        </div>
    </div>

@endsection
