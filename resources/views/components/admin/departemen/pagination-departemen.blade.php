@if ($paginator->hasPages())
    <nav class="w-full px-6 py-5" aria-label="Pagination">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 sm:gap-0">
            {{-- Info Teks --}}
            <div class="text-sm text-gray-600 dark:text-gray-400 text-center sm:text-left sm:flex-1">
                Menampilkan
                <span class="font-medium text-gray-800 dark:text-gray-200">{{ $paginator->firstItem() }}</span> -
                <span class="font-medium text-gray-800 dark:text-gray-200">{{ $paginator->lastItem() }}</span>
                dari
                <span class="font-medium text-gray-800 dark:text-gray-200">{{ $paginator->total() }}</span>
            </div>

            {{-- Kontrol Pagination --}}
            <div class="flex justify-center sm:justify-end sm:flex-1">
                <div class="flex items-center gap-2">

                    {{-- Tombol Sebelumnya --}}
                    @if ($paginator->onFirstPage())
                        <button disabled class="h-10 w-10 flex items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 text-gray-400 cursor-not-allowed transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" class="h-10 w-10 flex items-center justify-center rounded-full bg-white
                            dark:bg-gray-800 text-gray-700 dark:text-gray-300 border hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-600
                            dark:hover:text-blue-400 transition shadow-sm hover:shadow-md">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </a>
                    @endif

                    {{-- Nomor Halaman --}}
                    <div class="flex items-center gap-1 bg-white dark:bg-gray-800 px-2 py-1 rounded-full border border-gray-200 dark:border-gray-700">
                        @foreach ($elements as $element)
                            @if (is_string($element))
                                <span class="px-2 text-gray-400">...</span>
                            @endif

                            @if (is_array($element))
                                @foreach ($element as $page => $url)
                                    @php
                                        $show = (
                                            $page == 1 ||
                                            $page == $paginator->lastPage() ||
                                            abs($paginator->currentPage() - $page) <= 1
                                        );
                                    @endphp

                                    @if ($show)
                                        @if ($page == $paginator->currentPage())
                                            <span class="h-8 w-8 flex items-center justify-center rounded-full bg-blue-600 text-white font-semibold shadow transform scale-110">
                                                {{ $page }}
                                            </span>
                                        @else
                                            <a href="{{ $url }}" class="h-8 w-8 flex items-center justify-center rounded-full text-gray-700 dark:text-gray-300 hover:bg-blue-50
                                            dark:hover:bg-gray-700 hover:text-blue-600 dark:hover:text-blue-400 transition">
                                                {{ $page }}
                                            </a>
                                        @endif
                                    @elseif ($page == 2 || $page == $paginator->lastPage() - 1)
                                        <span class="px-2 text-gray-400">...</span>
                                    @endif
                                @endforeach
                            @endif
                        @endforeach
                    </div>

                    {{-- Tombol Berikutnya --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" class="h-10 w-10 flex items-center justify-center rounded-full bg-white dark:bg-gray-800 text-gray-700
                            dark:text-gray-300 border hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-600 dark:hover:text-blue-400 transition shadow-sm hover:shadow-md">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    @else
                        <button disabled class="h-10 w-10 flex items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 text-gray-400 cursor-not-allowed transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    @endif

                </div>
            </div>

        </div>
    </nav>
@endif
