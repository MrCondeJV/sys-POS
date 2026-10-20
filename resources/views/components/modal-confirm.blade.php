@props([
    'name' => null, // Identificador único o nombre del evento
    'title' => 'Confirmar acción',
    'message' => '¿Estás seguro de que deseas continuar con esta operación?',
    'consequence' => null, // Explicación explícita de las consecuencias de la acción
    'confirmText' => 'Confirmar',
    'cancelText' => 'Cancelar',
    'variant' => 'danger', // 'danger', 'warning' o 'primary'
    'confirmVariant' => null,
    'action' => null, // URL de acción del formulario si es POST/PUT/PATCH/DELETE estándar
    'method' => 'POST', // Método HTTP: POST, PUT, PATCH, DELETE
    'formId' => null, // ID opcional del formulario original para conservar referencias JS/DOM
    'formName' => null, // Name opcional del formulario original
    'show' => null, // Variable booleana Alpine externa opcional
])

@php
    $name = $name ?? 'modal_' . uniqid();
    $httpMethod = strtoupper(trim((string) $method));
    $confirmButtonVariant = $confirmVariant ?? ($variant === 'danger' ? 'danger' : 'primary');
    $hasExternalShow = !empty($show);

    $onConfirm = $attributes->get('@confirm') ?? $attributes->get('x-on:confirm');
    $onCancel = $attributes->get('@cancel') ?? $attributes->get('x-on:cancel');

    $closeExpr = $hasExternalShow ? ($show . ' = false') : 'open = false';
    $openExpr = $hasExternalShow ? ($show . ' = true') : 'open = true';
    $cancelClickExpr = ($onCancel ? ($onCancel . '; ') : '') . 'closeModal()';
    $confirmClickExpr = $onConfirm
        ? ($onConfirm . '; closeModal()')
        : ('$dispatch(\'confirmed-' . $name . '\'); $dispatch(\'confirm\'); closeModal()');
    $showExpr = $hasExternalShow ? $show : 'open';

    $cleanAttributes = $attributes->except([
        'confirm-text',
        'cancel-text',
        'confirm-variant',
        '@confirm',
        '@cancel',
        'x-on:confirm',
        'x-on:cancel',
        'show',
        'consequence',
        'form-id',
        'form-name',
    ]);
@endphp

<div
    x-data="{
        {{ $hasExternalShow ? '' : 'open: false,' }}
        _triggerEl: null,
        dynTitle: @js($title),
        dynMessage: @js($message),
        dynConsequence: @js($consequence),
        dynConfirmText: @js($confirmText),
        dynCancelText: @js($cancelText),
        dynVariant: @js($confirmButtonVariant),
        dynAction: @js($action),
        dynMethod: @js($httpMethod),
        dynFormId: @js($formId),
        dynFormName: @js($formName),
        get isOpen() { return Boolean({{ $showExpr }}); },
        focusSelector: 'button:not([disabled]), [href], input:not([disabled]):not([type=hidden]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex=\'-1\'])',
        openModal(trigger = null, payload = null) {
            this._triggerEl = (payload && payload.triggerEl) ? payload.triggerEl : (trigger || document.activeElement);
            if (payload && typeof payload === 'object') {
                if (payload.title !== undefined) this.dynTitle = payload.title;
                if (payload.message !== undefined) this.dynMessage = payload.message;
                if (payload.consequence !== undefined) this.dynConsequence = payload.consequence;
                if (payload.confirmText !== undefined) this.dynConfirmText = payload.confirmText;
                if (payload.cancelText !== undefined) this.dynCancelText = payload.cancelText;
                if (payload.variant !== undefined) this.dynVariant = payload.variant === 'danger' ? 'danger' : 'primary';
                if (payload.action !== undefined) this.dynAction = payload.action;
                if (payload.method !== undefined) this.dynMethod = String(payload.method).toUpperCase().trim();
                if (payload.formId !== undefined) this.dynFormId = payload.formId;
                if (payload.formName !== undefined) this.dynFormName = payload.formName;
            }
            {{ $openExpr }};
            this.$nextTick(() => this.focusFirst());
        },
        closeModal() {
            {{ $closeExpr }};
            this.$nextTick(() => {
                if (this._triggerEl && typeof this._triggerEl.focus === 'function') {
                    this._triggerEl.focus();
                }
            });
        },
        focusFirst() {
            const panel = this.$refs.dialogPanel;
            if (!panel) return;
            const focusables = panel.querySelectorAll(this.focusSelector);
            if (focusables.length) focusables[0].focus();
        },
        trapFocus(e) {
            if (!this.isOpen) return;
            const panel = this.$refs.dialogPanel;
            if (!panel) return;
            const focusables = Array.from(panel.querySelectorAll(this.focusSelector));
            if (!focusables.length) return;
            const first = focusables[0];
            const last = focusables[focusables.length - 1];
            if (e.shiftKey && document.activeElement === first) {
                e.preventDefault();
                last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        }
    }"
    x-init="$watch('{{ $showExpr }}', val => {
        if (val) {
            if (!_triggerEl) _triggerEl = document.activeElement;
            $nextTick(() => focusFirst());
        } else if (_triggerEl && typeof _triggerEl.focus === 'function') {
            $nextTick(() => _triggerEl.focus());
        }
    })"
    x-on:open-modal-{{ $name }}.window="openModal($event.detail?.triggerEl || null, $event.detail || null)"
    x-on:close-modal-{{ $name }}.window="closeModal()"
    x-on:keydown.escape.window="if (isOpen) { {{ $cancelClickExpr }}; }"
    x-on:keydown.tab.window="trapFocus($event)"
    {{ $cleanAttributes }}
>
    @if(isset($trigger))
        <div class="inline-flex" x-on:click="openModal($event.currentTarget.querySelector('button, a') || $event.currentTarget)">
            {{ $trigger }}
        </div>
    @endif

    <template x-teleport="body">
        <div
            x-show="{{ $showExpr }}"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title-{{ $name }}"
            aria-describedby="modal-desc-{{ $name }}"
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
                class="fixed inset-0 bg-brand-900/60 backdrop-blur-xs transition-opacity"
                x-on:click="{{ $cancelClickExpr }}"
            ></div>

            <!-- Modal Panel -->
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div
                    x-ref="dialogPanel"
                    x-show="{{ $showExpr }}"
                    x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative transform overflow-hidden rounded-xl bg-surface text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-border"
                    x-on:click.stop
                >
                    <div class="bg-surface p-6">
                        <div class="sm:flex sm:items-start gap-4">
                            <div
                                x-show="dynVariant === 'danger'"
                                class="mx-auto flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-danger-bg border border-danger-border sm:mx-0 sm:h-10 sm:w-10 text-danger"
                            >
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                </svg>
                            </div>
                            <div
                                x-show="dynVariant !== 'danger'"
                                class="mx-auto flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-info-bg border border-info-border sm:mx-0 sm:h-10 sm:w-10 text-brand-600"
                            >
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                                </svg>
                            </div>

                            <div class="mt-3 text-center sm:ml-0 sm:mt-0 sm:text-left flex-1">
                                <h3 class="text-base font-semibold leading-6 text-text-primary" id="modal-title-{{ $name }}" x-text="dynTitle">
                                    {{ $title }}
                                </h3>
                                <div class="mt-2 space-y-2" id="modal-desc-{{ $name }}">
                                    <p class="text-xs sm:text-[13px] text-text-secondary leading-relaxed" x-text="dynMessage">
                                        {{ $message }}
                                    </p>
                                    <div
                                        x-show="Boolean(dynConsequence)"
                                        x-text="dynConsequence"
                                        class="p-2.5 rounded-lg bg-surface-muted border border-border text-xs text-text-secondary"
                                    ></div>
                                    @if(isset($slot) && $slot->isNotEmpty())
                                        <div class="mt-3">
                                            {{ $slot }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-surface-muted px-6 py-3.5 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 border-t border-border">
                        <button
                            type="button"
                            x-on:click="{{ $cancelClickExpr }}"
                            class="btn btn-secondary"
                            x-text="dynCancelText"
                        >
                            {{ $cancelText }}
                        </button>

                        @if(isset($confirmAction))
                            {{ $confirmAction }}
                        @else
                            <template x-if="Boolean(dynAction)">
                                <form
                                    method="POST"
                                    :action="dynAction"
                                    :id="dynFormId || null"
                                    :name="dynFormName || null"
                                    class="inline"
                                >
                                    @csrf
                                    <template x-if="['PUT', 'PATCH', 'DELETE'].includes(dynMethod)">
                                        <input type="hidden" name="_method" :value="dynMethod">
                                    </template>
                                    @if(isset($formInputs))
                                        {{ $formInputs }}
                                    @endif
                                    <button
                                        type="submit"
                                        :class="dynVariant === 'danger' ? 'btn btn-danger' : 'btn btn-primary'"
                                        x-text="dynConfirmText"
                                    ></button>
                                </form>
                            </template>
                            <template x-if="!Boolean(dynAction)">
                                <button
                                    type="button"
                                    x-on:click="{{ $confirmClickExpr }}"
                                    :class="dynVariant === 'danger' ? 'btn btn-danger' : 'btn btn-primary'"
                                    x-text="dynConfirmText"
                                >
                                    {{ $confirmText }}
                                </button>
                            </template>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
