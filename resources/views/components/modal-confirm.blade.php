@props([
    'name' => null, // Unique identifier or event name
    'title' => 'Confirmar acción',
    'message' => '¿Estás seguro de que deseas continuar con esta operación?',
    'confirmText' => 'Confirmar',
    'cancelText' => 'Cancelar',
    'variant' => 'danger', // 'danger' or 'primary'
    'confirmVariant' => null,
    'action' => null, // Form action URL if standard POST/DELETE
    'method' => 'POST', // Form method
    'show' => null, // Alpine boolean variable to bind to open
])

@php
    $name = $name ?? 'modal_' . uniqid();
    $confirmButtonVariant = $confirmVariant ?? ($variant === 'danger' ? 'danger' : 'primary');
    $hasExternalShow = !empty($show);

    $onConfirm = $attributes->get('@confirm') ?? $attributes->get('x-on:confirm');
    $onCancel = $attributes->get('@cancel') ?? $attributes->get('x-on:cancel');

    $closeExpr = $hasExternalShow ? ($show . ' = false') : 'open = false';
    $openExpr = $hasExternalShow ? ($show . ' = true') : 'open = true';
    $cancelClickExpr = ($onCancel ? ($onCancel . '; ') : '') . $closeExpr;
    $confirmClickExpr = $onConfirm ? ($onConfirm . '; ' . $closeExpr) : ('$dispatch(\'confirmed-' . $name . '\'); $dispatch(\'confirm\'); ' . $closeExpr);
    $showExpr = $hasExternalShow ? $show : 'open';

    $cleanAttributes = $attributes->except(['confirm-text', 'cancel-text', 'confirm-variant', '@confirm', '@cancel', 'x-on:confirm', 'x-on:cancel', 'show']);
@endphp

<div
    x-data="{ {{ $hasExternalShow ? '' : 'open: false' }} }"
    x-on:open-modal-{{ $name }}.window="{{ $openExpr }}"
    x-on:close-modal-{{ $name }}.window="{{ $closeExpr }}"
    x-on:keydown.escape.window="{{ $closeExpr }}"
    {{ $cleanAttributes }}
>
    @if(isset($trigger))
        <div x-on:click="{{ $openExpr }}">
            {{ $trigger }}
        </div>
    @endif

    <template x-teleport="body">
        <div
            x-show="{{ $showExpr }}"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title-{{ $name }}"
            role="dialog"
            aria-modal="true"
        >
            <!-- Backdrop -->
            <div
                x-show="{{ $showExpr }}"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity"
                x-on:click="{{ $cancelClickExpr }}"
            ></div>

            <!-- Modal Panel -->
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div
                    x-show="{{ $showExpr }}"
                    x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200"
                    x-on:click.stop
                >
                    <div class="bg-white p-6">
                        <div class="sm:flex sm:items-start gap-4">
                            @if($confirmButtonVariant === 'danger')
                                <div class="mx-auto flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-rose-100 sm:mx-0 sm:h-10 sm:w-10 text-rose-600">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                    </svg>
                                </div>
                            @else
                                <div class="mx-auto flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10 text-brand-600">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                                    </svg>
                                </div>
                            @endif

                            <div class="mt-3 text-center sm:ml-0 sm:mt-0 sm:text-left flex-1">
                                <h3 class="text-base font-semibold leading-6 text-slate-900" id="modal-title-{{ $name }}">
                                    {{ $title }}
                                </h3>
                                <div class="mt-2">
                                    <p class="text-xs sm:text-[13px] text-slate-600 leading-relaxed">
                                        {{ $message }}
                                    </p>
                                    @if(isset($slot) && $slot->isNotEmpty())
                                        <div class="mt-3">
                                            {{ $slot }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-50 px-6 py-3.5 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 border-t border-slate-100">
                        <button
                            type="button"
                            x-on:click="{{ $cancelClickExpr }}"
                            class="inline-flex items-center justify-center font-medium rounded-lg transition border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 shadow-xs px-3.5 py-2 text-sm"
                        >
                            {{ $cancelText }}
                        </button>

                        @if($action)
                            <form method="POST" action="{{ $action }}" class="inline">
                                @csrf
                                @if(strtoupper($method) !== 'POST')
                                    @method($method)
                                @endif
                                <button
                                    type="submit"
                                    class="inline-flex items-center justify-center font-semibold rounded-lg transition shadow-xs px-3.5 py-2 text-sm {{ $confirmButtonVariant === 'danger' ? 'bg-rose-600 hover:bg-rose-700 text-white' : 'bg-brand-600 hover:bg-brand-700 text-white' }}"
                                >
                                    {{ $confirmText }}
                                </button>
                            </form>
                        @elseif(isset($confirmAction))
                            {{ $confirmAction }}
                        @else
                            <button
                                type="button"
                                x-on:click="{{ $confirmClickExpr }}"
                                class="inline-flex items-center justify-center font-semibold rounded-lg transition shadow-xs px-3.5 py-2 text-sm {{ $confirmButtonVariant === 'danger' ? 'bg-rose-600 hover:bg-rose-700 text-white' : 'bg-brand-600 hover:bg-brand-700 text-white' }}"
                            >
                                {{ $confirmText }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
