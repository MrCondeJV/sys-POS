@props([
    'padding' => 'p-5 sm:p-6', // 'p-0', 'p-4', 'p-5 sm:p-6', 'p-6'
    'title' => null,
    'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'card-banking overflow-hidden']) }}>
    @if($title || isset($header) || isset($actions))
        <div class="px-5 py-4 border-b border-slate-200/80 flex items-center justify-between gap-4 bg-white">
            <div>
                @if($title)
                    <h3 class="text-sm font-semibold text-slate-900 tracking-tight">
                        {{ $title }}
                    </h3>
                @endif
                @if($subtitle)
                    <p class="text-xs text-slate-500 mt-0.5">
                        {{ $subtitle }}
                    </p>
                @endif
                @if(isset($header))
                    {{ $header }}
                @endif
            </div>

            @if(isset($actions))
                <div class="flex items-center gap-2 shrink-0">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif

    <div class="{{ $padding }}">
        {{ $slot }}
    </div>

    @if(isset($footer))
        <div class="px-5 py-3 border-t border-slate-200/80 bg-slate-50/75 flex items-center justify-between gap-4">
            {{ $footer }}
        </div>
    @endif
</div>
