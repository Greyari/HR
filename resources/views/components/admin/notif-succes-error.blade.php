<!-- Wrapper Toast -->
<div id="custom-toast-wrapper" class="fixed bottom-6 right-6 z-[9999] space-y-3"></div>

<script>
function showToast(type, message) {
    const wrapper = document.getElementById('custom-toast-wrapper');
    if (!wrapper) return;

    const colors = {
        success: {
            border: 'border-emerald-200',
            bg: 'bg-gradient-to-br from-emerald-50 via-white to-white',
            iconBg: 'bg-emerald-500',
            ping: 'bg-emerald-200',
            bar: 'bg-emerald-500',
            title: 'Success!',
            icon: `<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />`,
            textColor: 'text-emerald-900',
            btnHover: 'hover:bg-emerald-500/10',
            closeColor: 'hover:text-emerald-500'
        },
        error: {
            border: 'border-rose-200',
            bg: 'bg-gradient-to-br from-rose-50 via-white to-white',
            iconBg: 'bg-rose-500',
            ping: 'bg-rose-200',
            bar: 'bg-rose-500',
            title: 'Oops! Something went wrong',
            icon: `<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />`,
            textColor: 'text-rose-900',
            btnHover: 'hover:bg-rose-500/10',
            closeColor: 'hover:text-rose-500'
        }
    };

    const c = colors[type] ?? colors.error;

    const toast = document.createElement('div');
    toast.className = `
        relative w-80 group overflow-hidden
        ${c.bg} ${c.border} border shadow-2xl rounded-xl
        ring-1 ring-black/5 backdrop-blur-md
        transition-all duration-500 ease-out
    `.trim();

    // Set Alpine attributes
    toast.setAttribute('x-data', '{ show: false }');
    toast.setAttribute('x-init', 'setTimeout(() => show = false, 10000)');
    toast.setAttribute('x-show', 'show');
    toast.setAttribute('x-transition:enter', 'transition ease-out duration-500');
    toast.setAttribute('x-transition:enter-start', 'opacity-0 scale-95 translate-x-10');
    toast.setAttribute('x-transition:enter-end', 'opacity-100 scale-100 translate-x-0');
    toast.setAttribute('x-transition:leave', 'transition ease-in duration-400');
    toast.setAttribute('x-transition:leave-start', 'opacity-100 scale-100 translate-x-0');
    toast.setAttribute('x-transition:leave-end', 'opacity-0 scale-90 translate-x-10');

    toast.setAttribute('aria-live', 'polite');

    // Isi konten toast
    toast.innerHTML = `
        <!-- Progress Bar -->
        <div x-data="{ progress: 100 }" x-init="
            const start = Date.now();
            const duration = 10000;
            const timer = setInterval(() => {
                const elapsed = Date.now() - start;
                progress = Math.max(0, 100 - (elapsed / duration) * 100);
                if (elapsed >= duration) clearInterval(timer);
            }, 10);
        " class="h-1 bg-opacity-30">
            <div x-bind:style="\`width: \${progress}%\`"
                class="h-full ${c.bar} transition-all ease-linear"></div>
        </div>

        <!-- Konten utama -->
        <div class="p-5 flex items-start gap-3">
            <!-- Ikon -->
            <div class="relative shrink-0">
                <div class="absolute inset-0 ${c.ping} rounded-full animate-ping opacity-50"></div>
                <div class="relative w-10 h-10 rounded-full ${c.iconBg} flex items-center justify-center text-white shadow-md">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        ${c.icon}
                    </svg>
                </div>
            </div>

            <!-- Teks -->
            <div class="flex-1 min-w-0">
                <h3 class="font-semibold ${c.textColor}">${c.title}</h3>
                <p class="text-sm text-gray-700 mt-1">${message}</p>
            </div>

            <!-- Tombol Close -->
            <button @click="show = false"
                class="absolute top-3 right-3 text-gray-400 ${c.closeColor} transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    `;

    wrapper.appendChild(toast);
    Alpine.initTree(toast);

    // ✅ Trigger animasi masuk setelah render
    requestAnimationFrame(() => {
        Alpine.$data(toast).show = true;
    });
}

// === Escape key untuk tutup semua toast aktif ===
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.querySelectorAll('#custom-toast-wrapper [x-data]').forEach(el => {
            const component = Alpine.$data(el);
            if (component && typeof component.show !== 'undefined') {
                component.show = false;
            }
        });
    }
});
</script>
