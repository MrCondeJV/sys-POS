@extends('layouts.app')

@section('title', 'Documento Electrónico ' . $documento->consecutivo_completo)

@section('content')
<div class="space-y-6">
    <x-page-header
        title="{{ $documento->consecutivo_completo }}"
        subtitle="{{ $documento->tipo->label() }} — Validación DIAN"
        :breadcrumbs="[
            ['label' => 'Facturación Electrónica', 'url' => route('facturacion-electronica.index')],
            ['label' => $documento->consecutivo_completo]
        ]">
        <x-slot:actions>
            @if($documento->xml_path)
                <a href="{{ route('facturacion-electronica.descargar-xml', $documento) }}" class="btn-secondary">
                    <svg class="h-4 w-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Descargar XML UBL 2.1
                </a>
            @endif
            <a href="{{ route('facturacion-electronica.index') }}" class="btn-secondary">
                Volver al Listado
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="max-w-5xl mx-auto space-y-6">
        <div class="card p-6 space-y-6">
            <div class="flex items-center justify-between pb-6 border-b border-slate-200">
                <div>
                    <span class="kpi-label">Estado de Validación DIAN</span>
                    <div class="text-base font-semibold text-slate-900 mt-1 flex items-center gap-2">
                        @if($documento->estado_dian === \App\Enums\EstadoDian::ACEPTADO)
                            <span class="badge badge-success">✓ {{ $documento->estado_dian->label() }}</span>
                        @elseif($documento->estado_dian === \App\Enums\EstadoDian::RECHAZADO)
                            <span class="badge badge-danger">✗ {{ $documento->estado_dian->label() }}</span>
                        @else
                            <span class="badge badge-warning">{{ $documento->estado_dian->label() }}</span>
                        @endif
                    </div>
                </div>
                <div class="text-right">
                    <span class="kpi-label">Fecha de Validación</span>
                    <div class="text-xs font-mono font-medium text-slate-800 mt-1">{{ $documento->fecha_validacion?->format('d/m/Y H:i:s') ?? 'Pendiente' }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <span class="kpi-label block mb-1">Código Único (CUFE / CUDE):</span>
                        <div class="p-3 bg-slate-50 rounded-lg border border-slate-200 font-mono text-xs text-slate-700 break-all select-all">
                            {{ $documento->cufe ?? 'No asignado' }}
                        </div>
                    </div>

                    <div>
                        <span class="kpi-label block mb-1">Mensaje Respuesta DIAN:</span>
                        <div class="p-3 bg-slate-50 rounded-lg border border-slate-200 text-xs text-slate-700">
                            {{ $documento->mensaje_dian ?? 'Sin mensaje' }}
                        </div>
                    </div>

                    <div>
                        <span class="kpi-label block mb-1">Resolución Asociada:</span>
                        <div class="p-3 bg-slate-50 rounded-lg border border-slate-200 text-xs text-slate-700">
                            {{ $documento->resolucion ? $documento->resolucion->numero_resolucion . ' (Prefijo ' . $documento->resolucion->prefijo . ')' : 'No aplica' }}
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <span class="kpi-label block mb-1">Cadena Técnica Código QR:</span>
                        <pre class="p-3 bg-slate-50 rounded-lg border border-slate-200 font-mono text-xs text-slate-600 overflow-x-auto whitespace-pre-wrap">{{ $documento->qr_data ?? 'No generado' }}</pre>
                    </div>
                </div>
            </div>

            @if($documento->tipo === \App\Enums\TipoDocumentoElectronico::FACTURA_ELECTRONICA && $documento->estado_dian === \App\Enums\EstadoDian::ACEPTADO && auth()->user()->can('documentos.anular'))
                <div class="pt-6 border-t border-slate-200" x-data="{ confirmNc: false }">
                    <h3 class="text-xs font-semibold text-slate-800 mb-2">Acciones Fiscales</h3>
                    <form x-ref="formNc" action="{{ route('facturacion-electronica.nota-credito', $documento) }}" method="POST" class="flex gap-2 items-center">
                        @csrf
                        <input type="text" name="motivo" required placeholder="Motivo de la nota crédito (ej. Anulación por devolución de mercancía)"
                            class="input text-xs flex-1">
                        <button type="button" @click="if ($refs.formNc.reportValidity()) confirmNc = true" class="btn-danger">
                            Emitir Nota Crédito
                        </button>
                    </form>

                    <template x-teleport="body">
                        <div x-show="confirmNc" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
                            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-[1px]" @click="confirmNc = false"></div>
                            <div class="relative z-10 w-full max-w-md bg-white rounded-xl border border-slate-200 shadow-lg p-6">
                                <h4 class="text-base font-semibold text-slate-900">¿Emitir Nota Crédito Electrónica?</h4>
                                <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">
                                    Se transmitirá una Nota Crédito Electrónica ante la DIAN para la factura {{ $documento->consecutivo_completo }}.
                                </p>
                                <div class="mt-5 flex justify-end gap-2.5">
                                    <button type="button" @click="confirmNc = false" class="btn-secondary">Cancelar</button>
                                    <button type="button" @click="$refs.formNc.submit()" class="btn-danger">Confirmar Emisión</button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            @endif
        </div>

        @if($xmlContent)
            <div class="card p-6">
                <h2 class="text-sm font-semibold text-slate-800 mb-3">XML Estructurado UBL 2.1</h2>
                <pre class="p-4 bg-slate-900 text-slate-100 rounded-lg font-mono text-xs overflow-x-auto">{{ $xmlContent }}</pre>
            </div>
        @endif
    </div>
</div>
@endsection
