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
                        <p class="text-gray-500 mt-1">Total <span id="totalDepartemen">{{ $departemen->total() }}</span> departemen terdaftar</p>
                    </div>

                    <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                        <!-- Input Pencarian -->
                        <div class="relative flex-1 md:w-64">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                </svg>
                            </div>

                            <input
                                id="searchInput" type="text" class="block w-full pl-10 pr-3 py-2.5 border border-gray-200 rounded-full bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Cari departemen..."
                            >
                        </div>

                        <!-- Tombol Tambah -->
                        @include('components.admin.departemen.modal-tambah-departemen')
                    </div>
                </div>

                <!-- Tabel Departemen -->
                <div class="p-6">
                    <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm">
                        <table class="min-w-full table-fixed divide-y divide-gray-200">
                            <thead class="bg-gray-800">
                                <tr>
                                    <th class="w-12 px-2 py-4 text-center text-xs font-medium text-gray-100 uppercase tracking-wide">No</th>
                                    <th class="px-4 py-4 text-center text-xs font-medium text-gray-100 uppercase tracking-wide">Nama Departemen</th>
                                    <th class="w-32 px-4 py-4 text-center text-xs font-medium text-gray-100 uppercase tracking-wide">Aksi</th>
                                </tr>
                            </thead>

                            <tbody id="tabelDepartemen" class="bg-white divide-y divide-gray-100">
                                    @include('components.admin.departemen.body-tabel-departemen')
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-100 flex justify-between items-center">
                    <div id="paginationWrapper">
                        {!! $departemen->links('components.admin.departemen.pagination-departemen') !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ajax Departemen -->
    @push('scripts')
        <script src="{{ asset('js/admin/admin-departemen.js') }}"></script>
    @endpush
@endsection
