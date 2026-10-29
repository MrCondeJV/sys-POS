@props([
    'responsive' => true,
])

{{-- @container: las columnas secundarias se ocultan según el ancho real disponible (@4xl:, @5xl:, @6xl:) --}}
<div {{ $attributes->merge(['class' => '@container w-full bg-surface border border-border rounded-xl overflow-hidden shadow-xs']) }}>
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
