// Timeout untuk debounce pencarian
let debounceTimeout = null;

// Fungsi untuk bind ulang semua link pagination
function bindPaginationLinks() {
    document.querySelectorAll('#paginationWrapper a').forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const keyword = document.getElementById('searchInput').value;
            fetchDepartemen(keyword, this.href);
        });
    });
}

// Fungsi utama untuk fetch data departemen (baik dari search atau pagination)
function fetchDepartemen(keyword = '', pageUrl = null) {
    let url;

    if (pageUrl) {
        const urlObj = new URL(pageUrl, window.location.origin);

        // Jika ada keyword, paksa path-nya ke endpoint pencarian
        if (keyword) {
            urlObj.pathname = '/admin/departemen/search';
            urlObj.searchParams.set('q', keyword);
        }

        url = urlObj.toString();
    } else {
        url = `/admin/departemen/search?q=${encodeURIComponent(keyword)}`;
    }

    // Ambil data via fetch AJAX
    fetch(url)
        .then(response => response.json())
        .then(data => {
            // Update konten tabel dan pagination
            document.getElementById('tabelDepartemen').innerHTML = data.html;
            document.getElementById('paginationWrapper').innerHTML = data.pagination;
            document.getElementById('totalDepartemen').textContent = data.total;

            // Bind ulang event pagination
            bindPaginationLinks();
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

// Event: input pada pencarian
document.getElementById('searchInput').addEventListener('input', function () {
    clearTimeout(debounceTimeout);
    const keyword = this.value.trim();

    debounceTimeout = setTimeout(() => {
        fetchDepartemen(keyword);
    }, 300);
});

// Event: klik pagination (delegasi)
document.addEventListener('click', function (e) {
    const link = e.target.closest('#paginationWrapper a');
    if (link) {
        e.preventDefault();
        const url = link.getAttribute('href');
        const keyword = document.getElementById('searchInput').value.trim();
        fetchDepartemen(keyword, url);
    }
});

// Event: halaman pertama kali dimuat
document.addEventListener('DOMContentLoaded', function () {
    bindPaginationLinks();
});
