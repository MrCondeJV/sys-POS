@props([
    'variant' => 'ghost', // ghost, secondary, outline, danger, primary
    'size' => 'sm', // sm (32px), md (36px) — área táctil garantizada de 40px
    'type' => 'button',
    'href' => null,
    'title' => null,
    'label' => null,
    'ariaLabel' => null,
    'icon' => null,
    'disabled' => false,
])

@php
    $accessibleLabel = $ariaLabel ?? $attributes->get('aria-label') ?? $title ?? $label ?? 'Acción';
    $tooltipTitle = $title ?? $label ?? $accessibleLabel;

    // Área táctil expandida a 40px mediante pseudo-elemento invisible (after:-inset-1)
    $baseClasses = 'relative inline-flex items-center justify-center rounded-lg font-medium transition-all select-none focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-1 disabled:opacity-50 disabled:cursor-not-allowed disabled:pointer-events-none shrink-0 after:content-[\'\'] after:absolute after:-inset-1';

    $sizeClasses = match ($size) {
        'md' => 'w-9 h-9 text-sm',
        default => 'w-8 h-8 text-xs', // 32px visual, 40px táctil
    };

    $variantClasses = match ($variant) {
        'secondary' => 'bg-surface text-text-primary border border-border-strong shadow-xs hover:bg-surface-muted focus-visible:ring-brand-600',
        'outline' => 'bg-transparent text-text-secondary border border-border-strong hover:bg-surface-muted hover:text-text-primary focus-visible:ring-brand-600',
        'danger' => 'bg-danger-bg text-danger border border-danger-border hover:bg-danger hover:text-white focus-visible:ring-danger',
        'primary' => 'bg-brand-600 text-white shadow-xs hover:bg-brand-500 focus-visible:ring-brand-600',
        default => 'bg-transparent text-text-secondary hover:bg-surface-muted hover:text-text-primary focus-visible:ring-brand-600', // ghost
    };

    $renderedIcon = null;
    if ($icon) {
        if (str_contains($icon, '<svg')) {
            $renderedIcon = $icon;
        } else {
            $renderedIcon = match ($icon) {
                'eye' => '<svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>',
                'pencil', 'edit' => '<svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>',
                'trash', 'delete' => '<svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>',
                'printer', 'print' => '<svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>',
                'download' => '<svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>',
                'plus' => '<svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>',
                'check' => '<svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>',
                'x' => '<svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>',
                'document', 'file', 'document-text' => '<svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>',
                'arrow-right' => '<svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>',
                'arrow-left' => '<svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>',
                'external-link' => '<svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>',
                'filter' => '<svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>',
                'search' => '<svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>',
                'refresh' => '<svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>',
                default => null,
            };
        }
    }

    $classes = "{$baseClasses} {$sizeClasses} {$variantClasses}";
    $tag = $href ? 'a' : 'button';
@endphp

<{{ $tag }}
    @if($href)
        href="{{ $href }}"
    @else
        type="{{ $type }}"
        @if($disabled) disabled @endif
    @endif
    title="{{ $tooltipTitle }}"
    aria-label="{{ $accessibleLabel }}"
    {{ $attributes->merge(['class' => $classes]) }}
>
    @if($renderedIcon)
        {!! $renderedIcon !!}
    @else
        {{ $slot }}
    @endif
</{{ $tag }}>
