@props([
    'variant' => 'text', // text, title, avatar, button, card, kpi, table-row
    'lines' => 1,
    'cols' => 4,
])

@if($variant === 'kpi')
    <div {{ $attributes->merge(['class' => 'kpi space-y-3']) }} role="status" aria-label="Cargando métrica">
        <div class="flex items-center justify-between">
            <div class="skeleton h-3 w-24"></div>
            <div class="skeleton h-5 w-5 rounded-md"></div>
        </div>
        <div class="skeleton h-7 w-32"></div>
        <div class="skeleton h-3 w-20"></div>
    </div>
@elseif($variant === 'card')
    <div {{ $attributes->merge(['class' => 'card p-5 space-y-3']) }} role="status" aria-label="Cargando contenido">
        <div class="skeleton h-4 w-1/3"></div>
        <div class="space-y-2 pt-1">
            @for($i = 0; $i < max(1, (int) $lines); $i++)
                <div class="skeleton h-3 {{ $i === $lines - 1 && $lines > 1 ? 'w-2/3' : 'w-full' }}"></div>
            @endfor
        </div>
    </div>
@elseif($variant === 'table-row')
    <tr {{ $attributes }} role="status" aria-label="Cargando fila">
        @for($i = 0; $i < max(1, (int) $cols); $i++)
            <td class="px-4 py-3">
                <div class="skeleton h-3.5 {{ $i === 0 ? 'w-32' : ($i === $cols - 1 ? 'w-16 ml-auto' : 'w-24') }}"></div>
            </td>
        @endfor
    </tr>
@elseif($variant === 'avatar')
    <div {{ $attributes->merge(['class' => 'skeleton h-9 w-9 rounded-full shrink-0']) }} role="status" aria-hidden="true"></div>
@elseif($variant === 'button')
    <div {{ $attributes->merge(['class' => 'skeleton h-9 w-28 rounded-lg shrink-0']) }} role="status" aria-hidden="true"></div>
@elseif($variant === 'title')
    <div {{ $attributes->merge(['class' => 'skeleton h-6 w-48']) }} role="status" aria-hidden="true"></div>
@else
    @if((int) $lines > 1)
        <div {{ $attributes->merge(['class' => 'space-y-2']) }} role="status" aria-label="Cargando texto">
            @for($i = 0; $i < (int) $lines; $i++)
                <div class="skeleton h-3.5 {{ $i === $lines - 1 ? 'w-3/4' : 'w-full' }}"></div>
            @endfor
        </div>
    @else
        <div {{ $attributes->merge(['class' => 'skeleton h-3.5 w-full']) }} role="status" aria-hidden="true"></div>
    @endif
@endif
