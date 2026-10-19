@props([
    'label' => null,
    'title' => null,
    'value',
    'icon' => null,
    'context' => null,
    'subtitle' => null,
    'trend' => null, // 'up', 'down', 'neutral'
    'trendValue' => null,
    'alert' => false, // false, 'warning', 'danger'
    'href' => null,
])

@php
    $displayLabel = $label ?? $title ?? '';
    $displayContext = $context ?? $subtitle ?? null;

    $alertBorder = match ($alert) {
        'warning', true => 'border-amber-300 ring-1 ring-amber-200/50 bg-amber-50/10',
        'danger' => 'border-rose-300 ring-1 ring-rose-200/50 bg-rose-50/10',
        default => '',
    };

    $trendColor = match ($trend) {
        'up' => 'text-emerald-700 bg-emerald-50 border-emerald-200',
        'down' => 'text-rose-700 bg-rose-50 border-rose-200',
        'neutral' => 'text-slate-600 bg-slate-100 border-slate-200',
        default => '',
    };

    $tag = $href ? 'a' : 'div';

    $renderedIcon = null;
    if ($icon) {
        if (str_contains($icon, '<svg')) {
            $renderedIcon = $icon;
        } elseif ($icon === 'users') {
            $renderedIcon = '<svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>';
        } elseif ($icon === 'check') {
            $renderedIcon = '<svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
        } elseif ($icon === 'credit-card') {
            $renderedIcon = '<svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>';
        } elseif ($icon === 'building') {
            $renderedIcon = '<svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>';
        } elseif ($icon === 'alert') {
            $renderedIcon = '<svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>';
        } elseif ($icon === 'currency-dollar') {
            $renderedIcon = '<svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
        }
    }
@endphp

<{{ $tag }}
    @if($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => "kpi-card relative flex flex-col justify-between transition-all " . ($href ? 'hover:border-brand-500 hover:shadow-md cursor-pointer ' : '') . $alertBorder]) }}
>
    <div class="flex items-start justify-between gap-3">
        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 truncate">
            {{ $displayLabel }}
        </span>
        @if($renderedIcon || isset($customIcon))
            <div class="text-slate-400 shrink-0">
                @if(isset($customIcon))
                    {{ $customIcon }}
                @else
                    {!! $renderedIcon !!}
                @endif
            </div>
        @endif
    </div>

    <div class="mt-2.5">
        <div class="text-2xl sm:text-[28px] font-semibold text-slate-900 tracking-tight tabular-nums leading-none">
            {{ $value }}
        </div>

        @if($displayContext || $trend || (isset($slot) && $slot->isNotEmpty()))
            <div class="mt-2.5 flex items-center flex-wrap gap-2 text-xs text-slate-500">
                @if($trend && $trendValue)
                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[11px] font-medium border tabular-nums {{ $trendColor }}">
                        @if($trend === 'up')
                            <svg class="w-3 h-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        @elseif($trend === 'down')
                            <svg class="w-3 h-3 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6" />
                            </svg>
                        @else
                            <svg class="w-3 h-3 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14" />
                            </svg>
                        @endif
                        <span>{{ $trendValue }}</span>
                    </span>
                @endif

                @if($displayContext)
                    <span class="truncate">{{ $displayContext }}</span>
                @endif

                @if(isset($slot) && $slot->isNotEmpty())
                    {{ $slot }}
                @endif
            </div>
        @endif
    </div>
</{{ $tag }}>
