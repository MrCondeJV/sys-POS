@props([
    'label',
    'value',
    'icon' => null,
    'context' => null,
    'trend' => null, // 'up', 'down', 'neutral'
    'trendValue' => null,
    'alert' => false, // false, 'warning', 'danger'
    'href' => null,
])

@php
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
@endphp

<{{ $tag }}
    @if($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => "kpi-card relative flex flex-col justify-between transition-all " . ($href ? 'hover:border-brand-500 hover:shadow-md cursor-pointer ' : '') . $alertBorder]) }}
>
    <div class="flex items-start justify-between gap-3">
        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 truncate">
            {{ $label }}
        </span>
        @if($icon || isset($customIcon))
            <div class="text-slate-400 shrink-0">
                @if(isset($customIcon))
                    {{ $customIcon }}
                @else
                    {!! $icon !!}
                @endif
            </div>
        @endif
    </div>

    <div class="mt-2.5">
        <div class="text-2xl sm:text-[28px] font-semibold text-slate-900 tracking-tight tabular-nums leading-none">
            {{ $value }}
        </div>

        @if($context || $trend || $slot->isNotEmpty())
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

                @if($context)
                    <span class="truncate">{{ $context }}</span>
                @endif

                {{ $slot }}
            </div>
        @endif
    </div>
</{{ $tag }}>
