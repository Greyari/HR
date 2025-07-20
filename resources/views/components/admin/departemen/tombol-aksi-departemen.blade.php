<div class="flex justify-center gap-3" x-data="{ showModal: false }" @keydown.escape.window="showModal = false">
    <!--Tombol edit-->
    <div class="relative group inline-block">
        @include('components.admin.departemen.modal-edit-departemen', ['departemen' => $item])
    </div>

    <!--Tombol hapus-->
    <div x-data="{ openModal: false }" @keydown.escape.window="openModal = false" class="relative group inline-block">

        <button
            @click="openModal = true"
            class="p-2 rounded-full bg-red-500 text-white hover:bg-red-600 transition-all shadow-md"
            type="button"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
        </button>

        <!-- Tooltip hapus-->
        <div class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 px-2 py-1 text-sm text-white bg-red-600 rounded shadow opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none">
            Hapus
        </div>

        <!-- Modal konfirmasi -->
        <div
            x-show="openModal"
            @click.self="openModal = false"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40"
        >
            <!-- Modal Content -->
            <div
                @click.stop
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 scale-90"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-90"
                class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md text-center"
            >

                <h2 class="text-xl font-semibold text-gray-800 mb-2">Konfirmasi Penghapusan</h2>
                <p class="text-gray-600 mb-4">Apakah kamu yakin ingin menghapus departemen <strong>{{ $item->nama_departemen }}</strong>?</p>

                <form action="{{ route('departemen.destroy', $item->id) }}" method="POST" class="flex justify-center gap-3 mt-4">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-full transition">
                        Ya, Hapus
                    </button>
                    <button type="button" @click="openModal = false" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-full transition">
                        Batal
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
