<!-- Success Notification -->
@if(session('success'))
<div
    x-data="{ show: false }"
    x-init="
        show = true;
        setTimeout(() => show = false, 10000);
    "
    x-show="show"
    x-transition:enter="transition ease-out duration-500"
    x-transition:enter-start="opacity-0 translate-y-10"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-5"
    class="fixed bottom-6 right-6 z-[9999]"
>
    <div
        @mouseenter="clearTimeout(timeout); timeout = setTimeout(() => show = false, 2000)"
        class="relative w-80 bg-gradient-to-br from-emerald-50 to-white shadow-xl rounded-xl overflow-hidden border border-emerald-100/50"
    >
        <!-- Progress Bar -->
        <div
            x-data="{ progress: 100 }"
            x-init="
                const start = Date.now();
                const duration = 10000;
                const timer = setInterval(() => {
                    const elapsed = Date.now() - start;
                    progress = Math.max(0, 100 - (elapsed / duration) * 100);
                    if (elapsed >= duration) clearInterval(timer);
                }, 10);
            "
            class="h-1 bg-emerald-200/50"
        >
            <div
                x-bind:style="`width: ${progress}%`"
                class="h-full bg-emerald-500 transition-all ease-linear"
            ></div>
        </div>

        <div class="p-5 flex items-start gap-3">
            <div class="relative">
                <div class="absolute inset-0 bg-emerald-100 rounded-full animate-ping"></div>
                <div class="relative w-10 h-10 rounded-full bg-emerald-500 flex items-center justify-center text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
            </div>

            <div class="flex-1">
                <h3 class="font-semibold text-emerald-900">Success!</h3>
                <p class="text-sm text-gray-600 mt-1">{{ session('success') }}</p>
                <div class="flex justify-end mt-3">
                    <button
                        @click="show = false"
                        class="text-xs px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-600 hover:bg-emerald-500/20 transition-colors"
                    >
                        Dismiss
                    </button>
                </div>
            </div>

            <button
                @click="show = false"
                class="text-gray-400 hover:text-gray-600 transition-colors"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    </div>
</div>
@endif

<!-- Error Notification -->
@if(session('error'))
<div
    x-data="{ show: false }"
    x-init="
        show = true;
        setTimeout(() => show = false, 10000);
    "
    x-show="show"
    x-transition:enter="transition ease-out duration-500"
    x-transition:enter-start="opacity-0 translate-y-10"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-5"
    class="fixed bottom-6 right-6 z-[9999]"
>
    <div
        @mouseenter="clearTimeout(timeout); timeout = setTimeout(() => show = false, 2000)"
        class="relative w-80 bg-gradient-to-br from-red-50 to-white shadow-xl rounded-xl overflow-hidden border border-red-100/50"
    >
        <!-- Progress Bar -->
        <div
            x-data="{ progress: 100 }"
            x-init="
                const start = Date.now();
                const duration = 10000;
                const timer = setInterval(() => {
                    const elapsed = Date.now() - start;
                    progress = Math.max(0, 100 - (elapsed / duration) * 100);
                    if (elapsed >= duration) clearInterval(timer);
                }, 10);
            "
            class="h-1 bg-red-200/50"
        >
            <div
                x-bind:style="`width: ${progress}%`"
                class="h-full bg-red-500 transition-all ease-linear"
            ></div>
        </div>

        <div class="p-5 flex items-start gap-3">
            <div class="relative">
                <div class="absolute inset-0 bg-red-100 rounded-full animate-ping"></div>
                <div class="relative w-10 h-10 rounded-full bg-red-500 flex items-center justify-center text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
            </div>

            <div class="flex-1">
                <h3 class="font-semibold text-red-900">Oops! Error</h3>
                <p class="text-sm text-gray-600 mt-1">{{ session('error') }}</p>
                <div class="flex justify-end mt-3">
                    <button
                        @click="show = false"
                        class="text-xs px-3 py-1 rounded-full bg-red-500/10 text-red-600 hover:bg-red-500/20 transition-colors"
                    >
                        Dismiss
                    </button>
                </div>
            </div>

            <button
                @click="show = false"
                class="text-gray-400 hover:text-gray-600 transition-colors"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    </div>
</div>
@endif
