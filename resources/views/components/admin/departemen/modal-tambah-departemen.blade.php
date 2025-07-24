<div id="modalTambahDepartemen"
     x-data="{
        showModal: false,
        init() {
            this.$watch('showModal', value => {
                if (value) {
                    this.$nextTick(() => {
                        const form = document.querySelector('#form-tambah-departemen');
                        form.querySelectorAll('[id^=error-]').forEach(el => el.innerHTML = '');
                        form.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500'));
                    });
                }
            });
        }
     }"
     @tutup-modal.window="showModal = false"
     x-ref="modalContainer"
     @keydown.escape.window="showModal = false">

    <button
        @click="showModal = true"
        class="flex items-center justify-center w-full gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-5 py-2.5 rounded-full shadow-md transition-all hover:shadow-lg transform hover:scale-95">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        Tambah Departemen
    </button>

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
            <h2 class="text-xl font-bold text-gray-800 mb-4">Tambah Departemen</h2>

            <form id="form-tambah-departemen">
                @csrf
                <div class="mb-4">
                    <label for="nama_departemen" class="block text-sm font-medium text-gray-700 mb-1">Nama Departemen</label>
                    <input type="text" id="nama_departemen_input" name="nama_departemen" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <!-- Pesan error -->
                    <p id="error-nama_departemen" class="px-4 text-sm text-red-600 mt-1"></p>
                </div>

                <div class="flex justify-end gap-2 mt-6">
                    <button
                        type="button"
                        @click="showModal = false"
                        class="px-4 py-2 rounded-md bg-gray-200 text-gray-700 hover:bg-gray-300 transition">
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 transition">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
