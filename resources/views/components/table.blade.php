@props([
    'responsive' => true,
])

<div {{ $attributes->merge(['class' => 'w-full bg-white border border-slate-200/90 rounded-lg overflow-hidden shadow-xs']) }}>
    <div class="{{ $responsive ? 'overflow-x-auto' : '' }}">
        <table class="table-banking">
            @if(isset($header) || isset($head))
                <thead>
                    {{ $header ?? $head }}
                </thead>
            @endif
            <tbody class="divide-y divide-slate-100">
                {{ $slot }}
            </tbody>
        </table>
    </div>

    @if(isset($footer) || isset($pagination))
        <div class="px-4 py-3 border-t border-slate-200/80 bg-slate-50/75 flex items-center justify-between gap-4">
            {{ $footer ?? $pagination }}
        </div>
    @endif
</div>
