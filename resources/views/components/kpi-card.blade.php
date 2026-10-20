@props([
    'label' => null,
    'title' => null,
    'value',
    'icon' => null,
    'context' => null,
    'subtitle' => null,
    'subtext' => null,
    'trend' => null, // 'up', 'down', 'neutral'
    'trendValue' => null,
    'alert' => false, // false, 'warning', 'danger'
    'status' => null,
    'variant' => null,
    'badge' => null,
    'href' => null,
])

@php
    $displayLabel = $label ?? $title ?? '';
    $displayContext = $context ?? $subtitle ?? $subtext ?? null;

    $resolvedAlert = $alert;
    if (!$resolvedAlert && in_array($status ?? $variant, ['warning', 'danger'], true)) {
        $resolvedAlert = $status ?? $variant;
    }

    $alertBorder = match ($resolvedAlert) {
        'warning', true => 'border-warning-border bg-warning-bg/20',
        'danger' => 'border-danger-border bg-danger-bg/20',
        default => '',
    };

    $trendColor = match ($trend) {
        'up' => 'text-success-text bg-success-bg border-success-border',
        'down' => 'text-danger-text bg-danger-bg border-danger-border',
        'neutral' => 'text-text-secondary bg-surface-muted border-border',
        default => '',
    };

    $tag = $href ? 'a' : 'div';

    $renderedIcon = null;
    if ($icon) {
        if (str_contains($icon, '<svg')) {
            $renderedIcon = $icon;
        } else {
            $renderedIcon = match ($icon) {
                'users' => '<svg class="w-5 h-5 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>',
                'check' => '<svg class="w-5 h-5 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
                'credit-card' => '<svg class="w-5 h-5 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>',
                'building' => '<svg class="w-5 h-5 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>',
                'alert' => '<svg class="w-5 h-5 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>',
                'currency-dollar', 'cash', 'coins' => '<svg class="w-5 h-5 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
                'trending-up' => '<svg class="w-5 h-5 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>',
                'shield' => '<svg class="w-5 h-5 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>',
                'calendar' => '<svg class="w-5 h-5 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>',
                default => null,
            };
        }
    }
@endphp

<{{ $tag }}
    @if($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => 'kpi relative flex flex-col justify-between transition-all ' . ($href ? 'hover:border-brand-500 cursor-pointer ' : '') . $alertBorder]) }}
>
    <div class="flex items-start justify-between gap-3">
        <span class="text-[11px] font-semibold uppercase tracking-wider text-text-muted truncate">
            {{ $displayLabel }}
        </span>
        @if($renderedIcon || isset($customIcon))
            <div class="text-text-muted shrink-0 w-5 h-5 flex items-center justify-center">
                @if(isset($customIcon))
                    {{ $customIcon }}
                @else
                    {!! $renderedIcon !!}
                @endif
            </div>
        @endif
    </div>

    <div class="mt-2.5">
        <div class="text-2xl sm:text-[28px] font-semibold text-text-primary tracking-tight tabular-nums leading-none">
            {{ $value }}
        </div>

        @if($displayContext || $trend || $badge || (isset($slot) && $slot->isNotEmpty()))
            <div class="mt-2.5 flex items-center flex-wrap gap-2 text-xs text-text-muted">
                @if($trend && $trendValue)
                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[11px] font-medium border tabular-nums {{ $trendColor }}">
                        @if($trend === 'up')
                            <svg class="w-3 h-3 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        @elseif($trend === 'down')
                            <svg class="w-3 h-3 text-danger" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6" />
                            </svg>
                        @else
                            <svg class="w-3 h-3 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14" />
                            </svg>
                        @endif
                        <span>{{ $trendValue }}</span>
                    </span>
                @endif

                @if($badge)
                    <span class="badge badge-neutral badge-sm">{{ $badge }}</span>
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
