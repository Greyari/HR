<div id="custom-toast-wrapper" class="fixed bottom-6 right-6 z-[9999] space-y-3"></div>

<script>
function showToast(type, message) {
    const wrapper = document.getElementById('custom-toast-wrapper');
    if (!wrapper) return;

    // Warna dan ikon berdasarkan tipe notifikasi
    const colors = {
        success: {
            border: 'border-emerald-100/50',
            bg: 'bg-gradient-to-br from-emerald-50 to-white',
            iconBg: 'bg-emerald-500',
            ping: 'bg-emerald-100',
            bar: 'bg-emerald-500',
            title: 'Success!',
            icon: `<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />`,
            textColor: 'text-emerald-900',
            btnHover: 'hover:bg-emerald-500/20',
        },
        error: {
            border: 'border-red-100/50',
            bg: 'bg-gradient-to-br from-red-50 to-white',
            iconBg: 'bg-red-500',
            ping: 'bg-red-100',
            bar: 'bg-red-500',
            title: 'Oops! Error',
            icon: `<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />`,
            textColor: 'text-red-900',
            btnHover: 'hover:bg-red-500/20',
        }
    };

    const c = colors[type] ?? colors.error;

    // Buat elemen toast
    const toast = document.createElement('div');
    toast.className = `
        relative w-80 ${c.bg} shadow-xl rounded-xl overflow-hidden
        border ${c.border} mb-3
    `.trim();

    // Tambahkan atribut Alpine.js untuk animasi dan auto-hide
    toast.setAttribute('x-data', '{ show: true }');
    toast.setAttribute('x-init', 'setTimeout(() => show = false, 10000)');
    toast.setAttribute('x-show', 'show');
    toast.setAttribute('x-transition:enter', 'transition ease-out duration-500');
    toast.setAttribute('x-transition:enter-start', 'opacity-0 translate-y-10');
    toast.setAttribute('x-transition:leave-end', 'opacity-0 translate-y-5');

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
        " class="h-1 bg-opacity-50">
            <div x-bind:style="\`width: \${progress}%\`"
                class="h-full ${c.bar} transition-all ease-linear"></div>
        </div>

        <!-- Konten utama -->
        <div class="p-5 flex items-start gap-3">
            <!-- Ikon -->
            <div class="relative">
                <div class="absolute inset-0 ${c.ping} rounded-full animate-ping"></div>
                <div class="relative w-10 h-10 rounded-full ${c.iconBg} flex items-center justify-center text-white">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        ${c.icon}
                    </svg>
                </div>
            </div>

            <!-- Teks -->
            <div class="flex-1">
                <h3 class="font-semibold ${c.textColor}">${c.title}</h3>
                <p class="text-sm text-gray-600 mt-1">${message}</p>

                <div class="flex justify-end mt-3">
                    <button @click="show = false"
                        class="text-xs px-3 py-1 rounded-full ${c.iconBg}/10 text-${type}-600 ${c.btnHover} transition-colors">
                        Dismiss
                    </button>
                </div>
            </div>
        </div>
    `;

    // Tambahkan ke DOM dan jalankan Alpine
    wrapper.appendChild(toast);
    Alpine.initTree(toast);
}
</script>
