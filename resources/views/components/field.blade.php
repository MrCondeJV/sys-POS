@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'required' => false,
    'hint' => null,
    'error' => null,
])

@php
    $fieldId = $id ?? $name ?? null;
    $errorsBag = isset($errors) && ($errors instanceof \Illuminate\Support\ViewErrorBag || $errors instanceof \Illuminate\Contracts\Support\MessageBag) ? $errors : null;
    $hasError = $errorsBag && $name ? $errorsBag->has($name) : !empty($error);
    $errorMessage = $error ?? ($errorsBag && $name ? $errorsBag->first($name) : null);
@endphp

<div {{ $attributes->merge(['class' => 'space-y-1.5']) }}>
    @if($label)
        <div class="flex items-center justify-between">
            <label
                @if($fieldId) for="{{ $fieldId }}" @endif
                class="block text-xs sm:text-[13px] font-medium text-slate-700 select-none"
            >
                {{ $label }}
                @if($required)
                    <span class="text-rose-500 font-bold ml-0.5" title="Campo obligatorio">*</span>
                @endif
            </label>

            @if(isset($action))
                <div class="text-xs">
                    {{ $action }}
                </div>
            @endif
        </div>
    @endif

    <div>
        {{ $slot }}
    </div>

    @if($hint && !$hasError)
        <p class="text-[12px] text-slate-500 mt-1">
            {{ $hint }}
        </p>
    @endif

    @if($hasError)
        <p class="text-[12px] text-rose-600 font-medium mt-1 flex items-center gap-1">
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <span>{{ $errorMessage }}</span>
        </p>
    @endif
</div>
