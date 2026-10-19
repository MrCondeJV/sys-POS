@props([
    'title',
    'subtitle' => null,
    'breadcrumb' => null,
    'breadcrumbs' => null,
])

@php
    $crumbs = $breadcrumbs ?? $breadcrumb;
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-slate-200/80 mb-6']) }}>
    <div class="space-y-1">
        @if($crumbs)
            <nav class="flex items-center gap-1.5 text-xs text-slate-500 mb-1" aria-label="Breadcrumb">
                @if(is_array($crumbs))
                    @foreach($crumbs as $index => $item)
                        @if($index > 0)
                            <span class="text-slate-300">/</span>
                        @endif
                        @if(is_array($item))
                            @if(!empty($item['url']))
                                <a href="{{ $item['url'] }}" class="hover:text-slate-800 transition">{{ $item['label'] ?? '' }}</a>
                            @else
                                <span class="text-slate-700 font-medium">{{ $item['label'] ?? '' }}</span>
                            @endif
                        @else
                            <span class="text-slate-700 font-medium">{{ $item }}</span>
                        @endif
                    @endforeach
                @else
                    {{ $crumbs }}
                @endif
            </nav>
        @endif
        <h1 class="text-xl sm:text-2xl font-semibold text-slate-900 tracking-tight">
            {{ $title }}
        </h1>
        @if($subtitle)
            <p class="text-xs sm:text-[13px] text-slate-500">
                {{ $subtitle }}
            </p>
        @endif
    </div>

    @if(isset($actions) || (isset($slot) && $slot->isNotEmpty()))
        <div class="flex items-center flex-wrap gap-2.5 shrink-0">
            {{ $actions ?? $slot }}
        </div>
    @endif
</div>
