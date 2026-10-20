@props([
    'padding' => 'p-5 sm:p-6', // 'p-0', 'p-4', 'p-5 sm:p-6', 'p-6'
    'title' => null,
    'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'card overflow-hidden']) }}>
    @if($title || isset($header) || isset($actions))
        <div class="px-5 py-4 border-b border-border flex items-center justify-between gap-4 bg-surface">
            <div>
                @if($title)
                    <h3 class="text-sm font-semibold text-text-primary tracking-tight">
                        {{ $title }}
                    </h3>
                @endif
                @if($subtitle)
                    <p class="text-xs text-text-secondary mt-0.5">
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
        <div class="px-5 py-3 border-t border-border bg-surface-muted flex items-center justify-between gap-4">
            {{ $footer }}
        </div>
    @endif
</div>
