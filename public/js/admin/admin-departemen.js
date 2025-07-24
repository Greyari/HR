// ========================
// Konstanta dan Variabel
// ========================

// Digunakan untuk menyimpan timeout saat user mengetik di input pencarian
let debounceTimeout = null;

// ========================
// Inisialisasi Awal
// ========================

// Setelah halaman dimuat, jalankan semua fungsi inisialisasi
document.addEventListener('DOMContentLoaded', function () {
    initSearchInput();            // Aktifkan fitur pencarian real-time
    bindPaginationLinks();       // Aktifkan klik pagination AJAX
    initFormTambahDepartemen();  // Tangani form tambah departemen tanpa reload
});

// ========================
// Inisialisasi Search Input
// ========================

// Fungsi untuk menyiapkan event pencarian real-time dengan debounce
function initSearchInput() {
    const searchInput = document.getElementById('searchInput');
    if (!searchInput) return;

    searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimeout); // Hapus timeout sebelumnya
        const keyword = this.value.trim(); // Ambil kata kunci dari input

        // Tunggu 300ms setelah user berhenti mengetik, baru fetch data
        debounceTimeout = setTimeout(() => {
            fetchDepartemen(keyword);
        }, 300);
    });
}

// ========================
// Bind Event Pagination
// ========================

// Fungsi untuk menangani klik link pagination agar tidak reload halaman
function bindPaginationLinks() {
    document.querySelectorAll('#paginationWrapper a').forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const keyword = document.getElementById('searchInput').value;
            fetchDepartemen(keyword, this.href); // Fetch halaman baru
        });
    });
}

// ========================
// Fetch Data Departemen
// ========================

// Ambil data tabel departemen dari server menggunakan AJAX
function fetchDepartemen(keyword = '', pageUrl = null) {
    let url;

    if (pageUrl) {
        const urlObj = new URL(pageUrl, window.location.origin);
        if (keyword) {
            urlObj.pathname = '/admin/departemen/search';
            urlObj.searchParams.set('q', keyword); // Tambah keyword ke URL
        }
        url = urlObj.toString();
    } else {
        // Default pencarian awal
        url = `/admin/departemen/search?q=${encodeURIComponent(keyword)}`;
    }

    fetch(url)
        .then(response => response.json())
        .then(data => updateTabelDepartemen(data)) // Update isi tabel
        .catch(error => console.error('Fetch error:', error));
}

// ========================
// Update UI Tabel Departemen
// ========================

// Fungsi untuk menampilkan data hasil fetch ke dalam elemen tabel
function updateTabelDepartemen(data) {
    document.getElementById('tabelDepartemen').innerHTML = data.html;
    document.getElementById('paginationWrapper').innerHTML = data.pagination;
    document.getElementById('totalDepartemen').textContent = data.total;

    bindPaginationLinks(); // Re-bind karena elemen pagination baru
}

// ========================
// Inisialisasi Form Tambah
// ========================

// Tangani submit form tambah departemen secara AJAX
function initFormTambahDepartemen() {
    const form = document.querySelector('#form-tambah-departemen');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault(); // Hindari reload

        const formData = new FormData(form);
        const currentPage = new URLSearchParams(window.location.search).get('page') || 1;
        formData.append('page', currentPage); // Kirim info halaman aktif

        fetch('/admin/departemen', {
            method: 'POST',
            body: formData
        })
            .then(res => {
                if (!res.ok) return res.json().then(err => { throw err }); // Tangani error validasi
                return res.json();
            })
            .then(data => handleSuccessSubmit(form, data)) // Jika sukses, update UI
            .catch(handleValidationErrors); // Jika error, tampilkan pesan
    });
}

// ========================
// Handle Submit Sukses
// ========================

// Fungsi untuk menangani hasil sukses submit form
function handleSuccessSubmit(form, data) {
    document.getElementById('tabelDepartemen').innerHTML = data.table;
    document.getElementById('paginationWrapper').innerHTML = data.pagination;
    document.getElementById('totalDepartemen').textContent = data.total;

    Alpine.initTree(document.getElementById('tabelDepartemen')); // Re-inisialisasi Alpine.js jika dipakai

    if (typeof bindEditButtons === 'function') {
        bindEditButtons(); // Kalau ada tombol edit dinamis, bind ulang
    }

    bindPaginationLinks(); // Re-bind pagination setelah update
    form.reset(); // Reset form setelah submit

    const searchInput = document.getElementById('searchInput');
    if (searchInput) searchInput.value = '';

    window.dispatchEvent(new CustomEvent('tutup-modal')); // Trigger tutup modal
    showToast(data.status, data.message); // Tampilkan notifikasi
}

// ========================
// Handle Error Validasi
// ========================

// Fungsi untuk menampilkan pesan error per input
function handleValidationErrors(error) {
    clearAllFieldErrors(); // Bersihkan error sebelumnya

    if (error.status === 'validation_error' && error.errors) {
        showToast(error.message, error.message); // Notif error umum
        displayFieldErrors(error.errors); // Tampilkan error spesifik per field
    } else {
        showToast(error.status || 'error', error.message || 'Terjadi kesalahan.');
    }
}

// ========================
// Helpers
// ========================

// Menghapus semua pesan error dan styling merah pada input
function clearAllFieldErrors() {
    document.querySelectorAll('input, textarea, select').forEach(input => {
        input.classList.remove('border-red-500');
        const errorEl = document.querySelector(`#error-${input.name}`);
        if (errorEl) errorEl.innerHTML = '';
    });
}

// Menampilkan pesan error validasi di bawah masing-masing input
function displayFieldErrors(errors) {
    Object.entries(errors).forEach(([field, messages]) => {
        const input = document.querySelector(`[name="${field}"]`);
        const errorEl = document.querySelector(`#error-${field}`);

        if (input) input.classList.add('border-red-500');
        if (errorEl) {
            errorEl.innerHTML = messages.map(msg => `<li>${msg}</li>`).join('');
        }
    });
}
