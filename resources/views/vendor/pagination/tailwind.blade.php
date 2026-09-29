@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex flex-col sm:flex-row items-center justify-between gap-4">
        
        <!-- Mobile/Small Screen view -->
        <div class="flex justify-between w-full sm:hidden gap-2">
            @if ($paginator->onFirstPage())
                <span class="relative inline-flex items-center px-4 py-2 text-xs font-bold text-slate-400 bg-slate-50 border border-slate-200 rounded-xl cursor-not-allowed">
                    {!! __('pagination.previous') !!}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 text-xs font-bold text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 hover:text-blue-600 transition-colors">
                    {!! __('pagination.previous') !!}
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="relative inline-flex items-center px-4 py-2 text-xs font-bold text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 hover:text-blue-600 transition-colors">
                    {!! __('pagination.next') !!}
                </a>
            @else
                <span class="relative inline-flex items-center px-4 py-2 text-xs font-bold text-slate-400 bg-slate-50 border border-slate-200 rounded-xl cursor-not-allowed">
                    {!! __('pagination.next') !!}
                </span>
            @endif
        </div>

        <!-- Desktop view -->
        <div class="hidden sm:flex flex-1 items-center justify-between">
            <div>
                <p class="text-xs text-slate-500 font-medium">
                    Menampilkan
                    @if ($paginator->firstItem())
                        <span class="font-bold text-slate-700">{{ $paginator->firstItem() }}</span>
                        -
                        <span class="font-bold text-slate-700">{{ $paginator->lastItem() }}</span>
                    @else
                        {{ $paginator->count() }}
                    @endif
                    dari
                    <span class="font-bold text-slate-700">{{ $paginator->total() }}</span>
                    data
                </p>
            </div>

            <div>
                <span class="inline-flex items-center gap-1 shadow-sm rounded-xl bg-white p-1 border border-slate-200/80">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                            <span class="inline-flex items-center justify-center w-8 h-8 text-slate-300 bg-transparent cursor-not-allowed rounded-lg" aria-hidden="true">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m15 18-6-6 6-6"/>
                                </svg>
                            </span>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center justify-center w-8 h-8 text-slate-600 bg-transparent hover:bg-slate-100 hover:text-blue-600 rounded-lg transition-colors cursor-pointer" aria-label="{{ __('pagination.previous') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m15 18-6-6 6-6"/>
                            </svg>
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span aria-disabled="true">
                                <span class="inline-flex items-center justify-center w-8 h-8 text-xs font-bold text-slate-400 bg-transparent cursor-default">{{ $element }}</span>
                            </span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page">
                                        <span class="inline-flex items-center justify-center w-8 h-8 text-xs font-black text-white bg-blue-600 rounded-lg shadow-sm shadow-blue-500/20 cursor-default">{{ $page }}</span>
                                    </span>
                                @else
                                    <a href="{{ $url }}" class="inline-flex items-center justify-center w-8 h-8 text-xs font-bold text-slate-600 bg-transparent hover:bg-slate-100 hover:text-blue-600 rounded-lg transition-colors cursor-pointer" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center justify-center w-8 h-8 text-slate-600 bg-transparent hover:bg-slate-100 hover:text-blue-600 rounded-lg transition-colors cursor-pointer" aria-label="{{ __('pagination.next') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m9 18 6-6-6-6"/>
                            </svg>
                        </a>
                    @else
                        <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                            <span class="inline-flex items-center justify-center w-8 h-8 text-slate-300 bg-transparent cursor-not-allowed rounded-lg" aria-hidden="true">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m9 18 6-6-6-6"/>
                                </svg>
                            </span>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif
