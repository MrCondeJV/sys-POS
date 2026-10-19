@props([
    'name', // Unique identifier or event name
    'title' => 'Confirmar acción',
    'message' => '¿Estás seguro de que deseas continuar con esta operación?',
    'confirmText' => 'Confirmar',
    'cancelText' => 'Cancelar',
    'variant' => 'danger', // 'danger' or 'primary'
    'action' => null, // Form action URL if standard POST/DELETE
    'method' => 'POST', // Form method
])

@php
    $confirmButtonVariant = $variant === 'danger' ? 'danger' : 'primary';
@endphp

<div
    x-data="{ open: false }"
    x-on:open-modal-{{ $name }}.window="open = true"
    x-on:close-modal-{{ $name }}.window="open = false"
    x-on:keydown.escape.window="open = false"
    {{ $attributes }}
>
    @if(isset($trigger))
        <div x-on:click="open = true">
            {{ $trigger }}
        </div>
    @endif

    <template x-teleport="body">
        <div
            x-show="open"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title-{{ $name }}"
            role="dialog"
            aria-modal="true"
        >
            <!-- Backdrop -->
            <div
                x-show="open"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity"
                x-on:click="open = false"
            ></div>

            <!-- Modal Panel -->
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div
                    x-show="open"
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
                            @if($variant === 'danger')
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
                                    @if($slot->isNotEmpty())
                                        <div class="mt-3">
                                            {{ $slot }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-50 px-6 py-3.5 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 border-t border-slate-100">
                        <x-button
                            variant="secondary"
                            size="md"
                            x-on:click="open = false"
                        >
                            {{ $cancelText }}
                        </x-button>

                        @if($action)
                            <form method="POST" action="{{ $action }}" class="inline">
                                @csrf
                                @if(strtoupper($method) !== 'POST')
                                    @method($method)
                                @endif
                                <x-button
                                    type="submit"
                                    :variant="$confirmButtonVariant"
                                    size="md"
                                >
                                    {{ $confirmText }}
                                </x-button>
                            </form>
                        @elseif(isset($confirmAction))
                            {{ $confirmAction }}
                        @else
                            <x-button
                                :variant="$confirmButtonVariant"
                                size="md"
                                x-on:click="$dispatch('confirmed-{{ $name }}'); open = false;"
                            >
                                {{ $confirmText }}
                            </x-button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
