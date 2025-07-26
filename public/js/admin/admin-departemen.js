// ========================
// Variabel Global
// ========================

// Timeout untuk debounce input pencarian
let debounceTimeout = null;
let baruSajaMenambahData = false;


// ========================
// Inisialisasi Setelah Halaman Siap
// ========================

document.addEventListener('DOMContentLoaded', () => {
    initSearchInput();            // Pencarian real-time
    bindPaginationLinks();       // Tangani pagination AJAX
    initFormTambahDepartemen();  // Form tambah via AJAX
    initFormEditDepartemen();    // Form edit via AJAX
    initFormHapusDepartemen();   // Tanganin aksi hapus
});

// ========================
// 1. Pencarian Real-Time
// ========================

function initSearchInput() {
    const searchInput = document.getElementById('searchInput');
    if (!searchInput) return;

    searchInput.addEventListener('input', () => {
        clearTimeout(debounceTimeout);
        const keyword = searchInput.value.trim();

        debounceTimeout = setTimeout(() => {
            fetchDepartemen(keyword); // Ambil data setelah delay 300ms
        }, 300);
    });
}

// ========================
// 2. Fetch Data Departemen
// ========================

function fetchDepartemen(keyword = '', pageUrl = null) {
    let url;

    if (pageUrl) {
        const urlObj = new URL(pageUrl, window.location.origin);
        if (keyword) {
            urlObj.pathname = '/admin/departemen/search';
            urlObj.searchParams.set('q', keyword);
        }
        url = urlObj.toString();
    } else {
        url = `/admin/departemen/search?q=${encodeURIComponent(keyword)}`;
    }

    // Simpan ke riwayat browser agar bisa Back/Forward
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
// 3. Update UI Tabel + Pagination
// ========================

function updateTabelDepartemen(data) {
    document.getElementById('tabelDepartemen').innerHTML = data.tabel;
    document.getElementById('paginationWrapper').innerHTML = data.pagination;
    document.getElementById('totalDepartemen').textContent = data.total;

    bindPaginationLinks();
    bindEditButtons();
    initFormEditDepartemen();
    initFormHapusDepartemen();
}

// ========================
// 4. Tangani Pagination Link
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
// 5. Form Tambah Departemen
// ========================

function initFormTambahDepartemen() {
    const form = document.querySelector('#form-tambah-departemen');

    if (form) {
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
                document.getElementById('tabelDepartemen').innerHTML = response.table;
                document.getElementById('paginationWrapper').innerHTML = response.pagination;
                document.getElementById('totalDepartemen').textContent = response.total;

                baruSajaMenambahData = true;

                document.getElementById('searchInput').value = '';
                baruSajaMenambahData = true;

                window.dispatchEvent(new CustomEvent('tutup-modal'));

                // Bind ulang
                bindPaginationLinks();
                bindEditButtons();
                initFormEditDepartemen();
                initFormHapusDepartemen();
                showToast(response.status, response.message);

                const currentUrl = new URL(window.location.href);
                const currentPage = currentUrl.searchParams.get('page') ?? '1';

                if (response.page_valid && response.page_valid !== currentPage) {
                    currentUrl.searchParams.set('page', response.page_valid);
                    history.replaceState({}, '', currentUrl.toString());
                }

                // Reset form
                form.reset();
            })
            .catch(handleValidationErrors);
        });
    }
}

// ========================
// 7. Form Edit Departemen
// ========================

// Tombol "Edit" akan mengisi form dan buka modal
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
                document.getElementById('tabelDepartemen').innerHTML = response.table;
                document.getElementById('paginationWrapper').innerHTML = response.pagination;

                bindEditButtons();
                bindPaginationLinks();
                initFormEditDepartemen();
                initFormHapusDepartemen();

                showToast(response.status, response.message);
            })
            .catch(handleValidationErrors);
        });
    });
}

// ========================
// 8. Validasi & Error Handling
// ========================

function handleValidationErrors(error) {
    clearAllFieldErrors();

    if (error.status === 'validation_error' && error.errors) {
        showToast(error.message, error.message);
        displayFieldErrors(error.errors);
    } else {
        showToast(error.status || 'error', error.message || 'Terjadi kesalahan.');
    }
}

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
// 9. Navigasi Browser (Back/Forward)
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

// ========================
// 10. Form Hapus Departemen
// ========================
function initFormHapusDepartemen() {
    document.querySelectorAll('.form-hapus-departemen').forEach(form => {
        form.addEventListener('submit', e => {
            e.preventDefault();

            const actionUrl = form.getAttribute('action');

            // Ambil data pencarian & halaman
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
                // Update tampilan
                document.getElementById('tabelDepartemen').innerHTML = response.table;
                document.getElementById('paginationWrapper').innerHTML = response.pagination;
                document.getElementById('totalDepartemen').textContent = response.total;

                // Re-bind semua event handler
                bindPaginationLinks();
                bindEditButtons();
                initFormEditDepartemen();
                initFormHapusDepartemen();
                showToast(response.status, response.message);

                // ✅ Perbarui URL jika page valid berbeda
                const currentUrl = new URL(window.location.href);
                const currentPage = currentUrl.searchParams.get('page') ?? '1';

                if (response.page_valid && response.page_valid !== currentPage) {
                    currentUrl.searchParams.set('page', response.page_valid);
                    history.replaceState({}, '', currentUrl.toString());
                }

                // Reset flag setelah penghapusan
                baruSajaMenambahData = false;
            })
            .catch(handleValidationErrors);
        });
    });
}


