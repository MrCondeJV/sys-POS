{{--
    Botón + modal para enviar una venta o cotización: correo con PDF adjunto o WhatsApp Web.
    WhatsApp Web (wa.me) no permite adjuntar archivos por enlace, así que el mensaje lleva un enlace
    corto y temporal al PDF; también se ofrece descargar el PDF para adjuntarlo a mano en el chat.
--}}
@props([
    'documento',      // App\Models\Venta|App\Models\Cotizacion
    'correoUrl',
    'pdfUrl',
])

@php
    $cliente = $documento->cliente;
    $mensajes = \App\Http\Controllers\EnvioDocumentoController::mensajes($documento);
    $etiqueta = $mensajes['etiqueta'];
    $mensajeCorreo = $mensajes['correo'];
    $mensajeWhatsApp = $mensajes['whatsapp'];
@endphp

<div x-data="{
        abierto: false,
        email: @js($cliente?->email ?? ''),
        telefono: @js(\App\Support\WhatsApp::normalizarTelefono($cliente?->telefono) ?? ''),
        mensajeWhatsApp: @js($mensajeWhatsApp),
        get telefonoLimpio() {
            let d = (this.telefono || '').replace(/\D+/g, '');
            if (d.length === 10 && d.startsWith('3')) d = '57' + d;
            return d;
        },
        get enlaceWhatsApp() {
            return 'https://wa.me/' + this.telefonoLimpio + '?text=' + encodeURIComponent(this.mensajeWhatsApp);
        }
    }" class="inline-block">
    <x-button variant="primary" size="sm" type="button" @click="abierto = true">
        <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
        </svg>
        Enviar
    </x-button>

    <div x-cloak x-show="abierto" @keydown.escape.window="abierto = false" class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="abierto = false"></div>

        <div class="relative bg-white rounded-xl shadow-xl border border-slate-200 w-full max-w-lg text-left max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-200">
                <h3 class="text-sm font-bold text-slate-900">Enviar {{ $etiqueta }}</h3>
                <button type="button" @click="abierto = false" class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg" aria-label="Cerrar">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div class="p-5 space-y-6">
                <!-- WhatsApp Web -->
                <div class="space-y-2">
                    <div class="text-xs font-bold text-slate-700 uppercase tracking-wider">WhatsApp</div>
                    <div>
                        <label class="label-base">Número del cliente</label>
                        <input type="tel" x-model="telefono" placeholder="Ej: 573001234567" class="input font-mono">
                        <p class="text-[11px] text-slate-500 mt-1">Con indicativo de país. A los celulares de 10 dígitos se les agrega el 57 automáticamente.</p>
                    </div>
                    <div>
                        <label class="label-base">Mensaje</label>
                        <textarea x-model="mensajeWhatsApp" rows="9" class="input text-xs font-mono leading-relaxed"></textarea>
                        <p class="text-[11px] text-slate-500 mt-1">
                            Incluye un enlace corto al PDF, vigente por {{ \App\Models\EnlaceCompartido::DIAS_VIGENCIA }} días. En WhatsApp, el texto entre *asteriscos* se ve en negrita.
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a :href="telefonoLimpio.length >= 10 ? enlaceWhatsApp : null" target="_blank" rel="noopener"
                           :class="telefonoLimpio.length >= 10 ? 'bg-emerald-600 hover:bg-emerald-700 text-white' : 'bg-slate-200 text-slate-400 pointer-events-none'"
                           class="inline-flex items-center px-3.5 py-2 rounded-lg text-xs font-semibold transition">
                            Abrir WhatsApp Web
                        </a>
                        <a href="{{ $pdfUrl }}" class="inline-flex items-center px-3.5 py-2 rounded-lg text-xs font-semibold border border-slate-300 text-slate-700 hover:bg-slate-50 transition">
                            Descargar PDF para adjuntar
                        </a>
                    </div>
                    <p class="text-[11px] text-slate-500">
                        Si quieres enviar el archivo además del enlace, descárgalo y adjúntalo manualmente en el chat de WhatsApp.
                    </p>
                </div>

                <!-- Correo -->
                <form action="{{ $correoUrl }}" method="POST" class="space-y-2 pt-5 border-t border-slate-200">
                    @csrf
                    <div class="text-xs font-bold text-slate-700 uppercase tracking-wider">Correo electrónico</div>
                    <div>
                        <label class="label-base">Correo del cliente</label>
                        <input type="email" name="email" x-model="email" required maxlength="150" placeholder="cliente@correo.com" class="input">
                    </div>
                    <div>
                        <label class="label-base">Mensaje</label>
                        <textarea name="mensaje" rows="5" required maxlength="2000" class="input text-xs">{{ $mensajeCorreo }}</textarea>
                    </div>
                    <button type="submit" class="btn-primary text-xs">Enviar correo con PDF adjunto</button>
                </form>
            </div>
        </div>
    </div>
</div>
