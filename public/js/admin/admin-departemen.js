// ========================
// VARIABEL GLOBAL
// ========================

// Digunakan untuk debounce (tunda fetch saat mengetik)
let debounceTimeout = null;
// Menandai apakah data baru saja ditambahkan (untuk logika hapus)
let baruSajaMenambahData = false;


// ========================
// INISIALISASI SAAT HALAMAN SIAP
// ========================

document.addEventListener('DOMContentLoaded', () => {
    initSearchInput();            // Aktifkan pencarian real-time
    bindPaginationLinks();       // Tangani klik pagination
    initFormTambahDepartemen();  // AJAX form tambah
    initFormEditDepartemen();    // AJAX form edit
    initFormHapusDepartemen();   // AJAX form hapus
});


// ========================
// FITUR PENCARIAN REALTIME
// ========================

function initSearchInput() {
    const searchInput = document.getElementById('searchInput');
    if (!searchInput) return;

    searchInput.addEventListener('input', () => {
        clearTimeout(debounceTimeout);

        const keyword = searchInput.value.trim();

        // Delay 300ms agar tidak fetch terlalu cepat
        debounceTimeout = setTimeout(() => {
            fetchDepartemen(keyword);
        }, 300);
    });
}


// ========================
// FETCH DATA DEPARTEMEN DARI SERVER
// ========================

function fetchDepartemen(keyword = '', pageUrl = null) {
    let url;

    if (pageUrl) {
        // Gunakan URL pagination, lalu inject keyword jika ada
        const urlObj = new URL(pageUrl, window.location.origin);
        if (keyword) {
            urlObj.pathname = '/admin/departemen/search';
            urlObj.searchParams.set('q', keyword);
        }
        url = urlObj.toString();
    } else {
        // Default URL pencarian
        url = `/admin/departemen/search?q=${encodeURIComponent(keyword)}`;
    }

    // Simpan state ke browser (untuk fitur back/forward)
    history.pushState({ keyword, url }, '', url);

    fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        updateTabelDepartemen(data);
        baruSajaMenambahData = false;
    })
    .catch(err => console.error('Fetch error:', err));
}


// ========================
// UPDATE TABEL + PAGINATION DI HALAMAN
// ========================

function updateTabelDepartemen(data) {
    document.getElementById('tabelDepartemen').innerHTML = data.tabel;
    document.getElementById('paginationWrapper').innerHTML = data.pagination;
    document.getElementById('totalDepartemen').textContent = data.total;

    // Re-bind semua aksi karena DOM sudah diganti
    bindPaginationLinks();
    bindEditButtons();
    initFormEditDepartemen();
    initFormHapusDepartemen();
}


// ========================
// PAGINATION LINK HANDLER
// ========================

function bindPaginationLinks() {
    document.querySelectorAll('#paginationWrapper a').forEach(link => {
        link.addEventListener('click', e => {
            e.preventDefault();

            const keyword = document.getElementById('searchInput').value;
            fetchDepartemen(keyword, link.href);
        });
    });
}


// ========================
// FORM TAMBAH DEPARTEMEN
// ========================

function initFormTambahDepartemen() {
    const form = document.querySelector('#form-tambah-departemen');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(form);
        const keyword = document.getElementById('searchInput')?.value ?? '';

        fetch('/admin/departemen', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData
        })
        .then(res => res.ok ? res.json() : res.json().then(err => { throw err }))
        .then(response => {
            // Update tampilan
            document.getElementById('tabelDepartemen').innerHTML = response.tabel;
            document.getElementById('paginationWrapper').innerHTML = response.pagination;
            document.getElementById('totalDepartemen').textContent = response.total;

            // Reset form & pencarian
            form.reset();
            document.getElementById('searchInput').value = '';
            baruSajaMenambahData = true;

            // Tutup modal (trigger event global)
            window.dispatchEvent(new CustomEvent('tutup-modal'));

            // Perbarui URL jika page berpindah
            const currentUrl = new URL(window.location.href);
            const currentPage = currentUrl.searchParams.get('page') ?? '1';

            if (response.page_valid && response.page_valid !== currentPage) {
                currentUrl.searchParams.set('page', response.page_valid);
                history.replaceState({}, '', currentUrl.toString());
            }

            // Re-bind handler
            bindPaginationLinks();
            bindEditButtons();
            initFormEditDepartemen();
            initFormHapusDepartemen();

            showToast(response.status, response.message);
        })
        .catch(handleValidationErrors);
    });
}


// ========================
// FORM EDIT DEPARTEMEN
// ========================

// Isi modal saat klik tombol edit
function bindEditButtons() {
    document.querySelectorAll('.btn-edit').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();

            const { id, nama } = this.dataset;
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nama_departemen').value = nama;

            document.getElementById('modal-edit')?.classList.remove('hidden');
        });
    });
}

// Submit form edit via AJAX
function initFormEditDepartemen() {
    document.querySelectorAll('form[id^="form-edit-departemen-"]').forEach(form => {
        form.addEventListener('submit', e => {
            e.preventDefault();

            const id = form.getAttribute('data-id');
            const formData = new FormData(form);
            formData.append('_method', 'PUT');

            // Sertakan pencarian dan halaman
            const keyword = document.getElementById('searchInput')?.value ?? '';
            const page = new URLSearchParams(window.location.search).get('page') ?? 1;
            formData.append('q', keyword);
            formData.append('page', page);

            fetch(`/departemen/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(res => res.ok ? res.json() : res.json().then(err => { throw err }))
            .then(response => {
                document.getElementById('tabelDepartemen').innerHTML = response.tabel;
                document.getElementById('paginationWrapper').innerHTML = response.pagination;
                document.getElementById('totalDepartemen').textContent = response.total;

                // Re-bind semua handler
                bindPaginationLinks();
                bindEditButtons();
                initFormEditDepartemen();
                initFormHapusDepartemen();

                showToast(response.status, response.message);
            })
            .catch(handleValidationErrors);
        });
    });
}


// ========================
// FORM HAPUS DEPARTEMEN
// ========================

function initFormHapusDepartemen() {
    document.querySelectorAll('.form-hapus-departemen').forEach(form => {
        form.addEventListener('submit', e => {
            e.preventDefault();

            const actionUrl = form.getAttribute('action');
            const keyword = document.getElementById('searchInput')?.value ?? '';
            const page = new URLSearchParams(window.location.search).get('page') ?? 1;

            fetch(actionUrl, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    q: keyword,
                    page: page,
                    recently_added: baruSajaMenambahData
                })
            })
            .then(res => res.ok ? res.json() : res.json().then(err => { throw err }))
            .then(response => {
                // Update UI
                document.getElementById('tabelDepartemen').innerHTML = response.tabel;
                document.getElementById('paginationWrapper').innerHTML = response.pagination;
                document.getElementById('totalDepartemen').textContent = response.total;

                // Update URL jika page berubah
                const currentUrl = new URL(window.location.href);
                const currentPage = currentUrl.searchParams.get('page') ?? '1';

                if (response.page_valid && response.page_valid !== currentPage) {
                    currentUrl.searchParams.set('page', response.page_valid);
                    history.replaceState({}, '', currentUrl.toString());
                }

                baruSajaMenambahData = false;

                // Re-bind
                bindPaginationLinks();
                bindEditButtons();
                initFormEditDepartemen();
                initFormHapusDepartemen();

                showToast(response.status, response.message);
            })
            .catch(handleValidationErrors);
        });
    });
}


// ========================
// VALIDASI DAN ERROR HANDLING
// ========================

// Tangani error validasi / error umum
function handleValidationErrors(error) {
    clearAllFieldErrors();

    if (error.status === 'validation_error' && error.errors) {
        showToast(error.message, error.message);
        displayFieldErrors(error.errors);
    } else {
        showToast(error.status || 'error', error.message || 'Terjadi kesalahan.');
    }
}

// Hapus semua pesan error sebelumnya
function clearAllFieldErrors() {
    document.querySelectorAll('form').forEach(form => {
        form.querySelectorAll('input, textarea, select').forEach(input => {
            input.classList.remove('border-red-500');

            const id = form.getAttribute('data-id');
            const errorEl = form.querySelector(`#error-${input.name}${id ? '_' + id : ''}`);
            if (errorEl) errorEl.innerHTML = '';
        });
    });
}

// Tampilkan pesan error per field
function displayFieldErrors(errors) {
    Object.entries(errors).forEach(([field, messages]) => {
        document.querySelectorAll(`[name="${field}"]`).forEach(input => {
            const form = input.closest('form');
            const id = form?.getAttribute('data-id');
            const errorEl = form?.querySelector(`#error-${field}${id ? '_' + id : ''}`);

            if (input) input.classList.add('border-red-500');
            if (errorEl) errorEl.innerHTML = messages.map(msg => `<li>${msg}</li>`).join('');
        });
    });
}


// ========================
// NAVIGASI BACK/FORWARD (POPSTATE)
// ========================

window.addEventListener('popstate', event => {
    const keyword = document.getElementById('searchInput')?.value || '';
    const url = event.state?.url || window.location.href;

    if (url.includes('/admin/departemen/search')) {
        fetchDepartemen(keyword, url);
    } else {
        window.location.href = url;
    }
});
