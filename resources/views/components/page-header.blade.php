@props([
    'title',
    'subtitle' => null,
    'description' => null,
    'breadcrumb' => null,
    'breadcrumbs' => null,
])

@php
    $crumbs = $breadcrumbs ?? $breadcrumb;
    $displaySubtitle = $subtitle ?? $description;
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-5 border-b border-border mb-6']) }}>
    <div class="space-y-1 min-w-0">
        @if($crumbs)
            <nav class="flex items-center gap-1.5 text-xs text-text-secondary mb-1" aria-label="Breadcrumb">
                @if(is_array($crumbs))
                    @foreach($crumbs as $index => $item)
                        @if($index > 0)
                            <span class="text-border-strong" aria-hidden="true">/</span>
                        @endif
                        @if(is_array($item))
                            @if(!empty($item['url']))
                                <a href="{{ $item['url'] }}" class="hover:text-text-primary transition">{{ $item['label'] ?? '' }}</a>
                            @else
                                <span class="text-text-primary font-medium">{{ $item['label'] ?? '' }}</span>
                            @endif
                        @else
                            <span class="text-text-primary font-medium">{{ $item }}</span>
                        @endif
                    @endforeach
                @else
                    {{ $crumbs }}
                @endif
            </nav>
        @endif
        <h1 class="text-xl sm:text-2xl font-semibold text-text-primary tracking-tight truncate">
            {{ $title }}
        </h1>
        @if($displaySubtitle)
            <p class="text-xs sm:text-[13px] text-text-secondary">
                {{ $displaySubtitle }}
            </p>
        @endif
    </div>

    @if(isset($actions) || (isset($slot) && $slot->isNotEmpty()))
        <div class="flex items-center flex-wrap gap-2.5 shrink-0">
            {{ $actions ?? $slot }}
        </div>
    @endif
</div>
