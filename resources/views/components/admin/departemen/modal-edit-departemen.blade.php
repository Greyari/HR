@php
    $id = $departemen->id;
    $nama = $departemen->nama_departemen;
@endphp

<div
    x-data="{
        showModal: false,
        init() {
            this.$watch('showModal', value => {
                if (value) {
                    this.$nextTick(() => {
                        const form = document.querySelector('#form-edit-departemen-{{ $id }}');
                        // Bersihkan semua pesan error dan border merah
                        form.querySelectorAll('[id^=error-]').forEach(el => el.innerHTML = '');
                        form.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500'));
                    });
                }
            });
        }
    }"
    @keydown.escape.window="showModal = false">

    <!-- Tombol Edit -->
    <button @click="showModal = true" class="p-2 rounded-full bg-yellow-500 text-white hover:bg-yellow-600 transition-all shadow-md" type="button">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
        </svg>
    </button>

    <!-- Tooltip edit-->
    <div class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 px-2 py-1 text-xs text-white
              bg-yellow-600 rounded shadow opacity-0 scale-95 group-hover:opacity-100 group-hover:scale-100 transition-all duration-200 pointer-events-none">
        Edit
    </div>

    <!-- Modal Overlay -->
    <div
        x-show="showModal"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black bg-opacity-40 z-50 flex items-center justify-center"
        @click.self="showModal = false"
    >
        <!-- Modal Konten -->
        <div
            @click.stop
            x-show="showModal"
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="opacity-0 scale-90"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-90"
            class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md relative z-50"
        >
            <h2 class="text-xl font-bold text-gray-800 mb-4">Edit Departemen</h2>

            <form id="form-edit-departemen-{{ $id }}" data-id="{{ $id }}">
                @csrf
                <div class="mb-4">
                    <label for="nama_departemen_{{ $id }}" class="block text-sm font-medium text-gray-700 mb-1">Nama Departemen</label>
                    <input type="text"
                        id="nama_departemen_{{ $id }}"
                        name="nama_departemen"
                        value="{{ $nama }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >

                    <!-- Pesan error -->
                    <p id="error-nama_departemen_{{ $id }}" class="px-4 text-sm text-red-600 mt-1"></p>
                </div>

                <div class="flex justify-end gap-2 mt-6">
                    <button
                        type="button"
                        @click="showModal = false"
                        class="px-4 py-2 rounded-md bg-gray-200 text-gray-700 hover:bg-gray-300 transition">
                        Batal
                    </button>
                    <button
                        type="button"
                        class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 transition">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
