<div id="modalTambahDepartemen"
     x-data="{ showModal: false }"
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Ambil form tambah departemen berdasarkan ID
    const form = document.querySelector('#form-tambah-departemen');

    if (form) {
        // Saat form disubmit
        form.addEventListener('submit', function (e) {
            e.preventDefault(); // Hindari reload bawaan form

            // Ambil data dari form
            const formData = new FormData(form);

            // Ambil halaman saat ini dari URL (agar bisa kembali ke halaman tersebut)
            const currentPage = new URLSearchParams(window.location.search).get('page') || 1;
            formData.append('page', currentPage); // Kirim halaman saat ini ke server

            // Kirim request AJAX POST ke server
            fetch('/admin/departemen', {
                method: 'POST',
                body: formData
            })
            .then(res => {
                // Jika response bukan 2xx, lempar error JSON
                if (!res.ok) {
                    return res.json().then(err => { throw err });
                }
                return res.json(); // Parse response sebagai JSON
            })
            .then(data => {
                // Ganti isi tabel dan pagination dengan data terbaru
                document.getElementById('tabelDepartemen').innerHTML = data.table;
                document.getElementById('paginationWrapper').innerHTML = data.pagination;
                document.getElementById('totalDepartemen').textContent = data.total;

                // Inisialisasi ulang komponen Alpine di dalam tabel
                Alpine.initTree(document.getElementById('tabelDepartemen'));

                // Jika fungsi edit ada, panggil ulang (penting kalau ada tombol edit baru)
                if (typeof bindEditButtons === 'function') {
                    bindEditButtons();
                }

                // Re-bind pagination agar tetap bisa diklik
                bindPaginationLinks();

                // Reset form agar kosong lagi setelah submit
                form.reset();

                // Kirim event ke Alpine (untuk menutup modal)
                window.dispatchEvent(new CustomEvent('tutup-modal'));

                // Tampilkan notifikasi sukses
                setTimeout(() => {
                    alert('Departemen berhasil ditambahkan!');
                }, 400);
            })
            .catch(err => {
                // Tangani jika error dari server
                console.error(err);
                alert('Gagal menambah departemen');
            });

            // Scroll otomatis ke baris terakhir setelah submit
            setTimeout(() => {
                const lastRow = document.querySelector('#tabelDepartemen tbody tr:last-child');
                if (lastRow) {
                    lastRow.scrollIntoView({ behavior: 'smooth' });
                }
            }, 500);

        });
    }
});
</script>
@endpush
