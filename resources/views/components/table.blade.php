@props([
    'responsive' => true,
])

<div {{ $attributes->merge(['class' => 'w-full bg-surface border border-border rounded-xl overflow-hidden shadow-xs']) }}>
    <div class="{{ $responsive ? 'overflow-x-auto' : '' }}">
        <table class="table tabular-nums">
            @if(isset($header) || isset($head) || isset($thead))
                <thead>
                    {{ $header ?? $head ?? $thead }}
                </thead>
            @endif
            <tbody class="divide-y divide-border">
                {{ $slot }}
            </tbody>
        </table>
    </div>

    @if(isset($footer) || isset($pagination))
        <div class="px-4 py-3 border-t border-border bg-surface-muted flex items-center justify-between gap-4">
            {{ $footer ?? $pagination }}
        </div>
    @endif
</div>
