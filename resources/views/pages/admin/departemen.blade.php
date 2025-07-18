@extends('layouts.mainAdmin')

@section('content')
<div class="bg-gradient-to-br bg-gray-50 p-4 md:p-8">
    <div class="mx-auto">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 mb-8">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-800">Departemen Management</h1>
                <p class="text-lg text-gray-600 mt-2">Kelola struktur organisasi perusahaan Anda</p>
            </div>
        </div>

        <!-- Card Container -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <!-- Card Header with Actions -->
            <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="flex-1">
                    <h2 class="text-xl font-semibold text-gray-800">Daftar Departemen</h2>
                    <p class="text-gray-500 mt-1">Total 3 departemen terdaftar</p>
                </div>

                <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                    <div class="relative flex-1 md:w-64">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <input type="text" class="block w-full pl-10 pr-3 py-2.5 border border-gray-200 rounded-full bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Cari departemen...">
                    </div>

                    <button class="flex items-center justify-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-5 py-2.5 rounded-full shadow-md transition-all hover:shadow-lg transform hover:scale-95">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Tambah Departemen
                    </button>
                </div>
            </div>

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
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-2 py-3 text-center">1</td>
                                <td class="px-4 py-3 text-left">HRD (Human Resources Department)</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-center gap-3">
                                        <button class="p-2 rounded-full bg-yellow-500 text-white hover:bg-yellow-600 transition-all shadow-md">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                            </svg>
                                        </button>
                                        <button class="p-2 rounded-full bg-red-500 text-white hover:bg-red-600 transition-all shadow-md">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-100 flex flex-col md:flex-row items-center justify-between">
                <div class="text-sm text-gray-500 mb-4 md:mb-0">
                    Showing <span class="font-medium">1</span> to <span class="font-medium">3</span> of <span class="font-medium">3</span> results
                </div>
                <div class="flex items-center space-x-2">
                    <button class="px-4 py-2 border rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200 disabled:opacity-50" disabled>
                        Previous
                    </button>
                    <button class="px-4 py-2 border rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition-colors duration-200">
                        1
                    </button>
                    <button class="px-4 py-2 border rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200 disabled:opacity-50" disabled>
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
