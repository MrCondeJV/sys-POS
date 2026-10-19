@props([
    'title',
    'subtitle' => null,
    'breadcrumb' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-slate-200/80 mb-6']) }}>
    <div class="space-y-1">
        @if($breadcrumb || isset($breadcrumbs))
            <nav class="flex items-center gap-1.5 text-xs text-slate-500 mb-1" aria-label="Breadcrumb">
                {{ $breadcrumbs ?? $breadcrumb }}
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

    @if(isset($actions) || $slot->isNotEmpty())
        <div class="flex items-center flex-wrap gap-2.5 shrink-0">
            {{ $actions ?? $slot }}
        </div>
    @endif
</div>
