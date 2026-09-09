@extends('layouts.app')

@section('content')
<div class=\"mb-6\">
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-800 tracking-tight">{{ $documento->consecutivo_completo }}</h1>
                <p class="text-sm text-slate-500 font-medium">{{ $documento->tipo->label() }} — Validación DIAN</p>
            </div>
            <div class="flex gap-2">
                @if($documento->xml_path)
                    <a href="{{ route('facturacion-electronica.descargar-xml', $documento) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl transition shadow-sm">
                        📥 Descargar XML UBL 2.1
                    </a>
                @endif
                <a href="{{ route('facturacion-electronica.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold rounded-xl transition">
                    Volver al Listado
                </a>
            </div>
        </div>
</div>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6">
                <div class="flex items-center justify-between pb-6 border-b border-slate-100">
                    <div>
                        <div class="text-xs uppercase font-bold text-slate-400">Estado de Validación DIAN</div>
                        <div class="text-xl font-bold text-slate-800 mt-1 flex items-center gap-2">
                            @if($documento->estado_dian === \App\Enums\EstadoDian::ACEPTADO)
                                <span class="text-emerald-600 font-black">✓ {{ $documento->estado_dian->label() }}</span>
                            @else
                                <span class="text-rose-600 font-black">{{ $documento->estado_dian->label() }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs uppercase font-bold text-slate-400">Fecha de Validación</div>
                        <div class="text-sm font-bold text-slate-700 mt-1">{{ $documento->fecha_validacion?->format('d/m/Y H:i:s') ?? 'Pendiente' }}</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6">
                    <div class="space-y-4">
                        <div>
                            <span class="text-xs uppercase font-bold text-slate-400">Código Único (CUFE / CUDE):</span>
                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 font-mono text-xs text-slate-700 break-all select-all mt-1">
                                {{ $documento->cufe ?? 'No asignado' }}
                            </div>
                        </div>

                        <div>
                            <span class="text-xs uppercase font-bold text-slate-400">Mensaje Respuesta DIAN:</span>
                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 text-sm text-slate-700 mt-1">
                                {{ $documento->mensaje_dian ?? 'Sin mensaje' }}
                            </div>
                        </div>

                        <div>
                            <span class="text-xs uppercase font-bold text-slate-400">Resolución Asociada:</span>
                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 text-sm text-slate-700 mt-1">
                                {{ $documento->resolucion ? $documento->resolucion->numero_resolucion . ' (Prefijo ' . $documento->resolucion->prefijo . ')' : 'No aplica' }}
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <span class="text-xs uppercase font-bold text-slate-400">Cadena Técnica Código QR:</span>
                            <pre class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 font-mono text-xs text-slate-600 overflow-x-auto mt-1 whitespace-pre-wrap">{{ $documento->qr_data ?? 'No generado' }}</pre>
                        </div>
                    </div>
                </div>

                @if($documento->tipo === \App\Enums\TipoDocumentoElectronico::FACTURA_ELECTRONICA && $documento->estado_dian === \App\Enums\EstadoDian::ACEPTADO)
                    <div class="pt-6 border-t border-slate-100 mt-6">
                        <h3 class="text-sm font-bold text-slate-800 mb-2">Acciones Fiscales</h3>
                        <form action="{{ route('facturacion-electronica.nota-credito', $documento) }}" method="POST" class="flex gap-2 items-center" onsubmit="return confirm('¿Confirma la emisión de una Nota Crédito Electrónica ante la DIAN para esta factura?');">
                            @csrf
                            <input type="text" name="motivo" required placeholder="Motivo de la nota crédito (ej. Anulación por devolución de mercancía)" class="flex-1 rounded-xl border-slate-200 text-sm focus:ring-rose-500 focus:border-rose-500">
                            <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold rounded-xl transition shadow-sm">
                                Emitir Nota Crédito Electrónica
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            @if($xmlContent)
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6">
                    <h2 class="text-lg font-bold text-slate-800 mb-3">XML Estructurado UBL 2.1</h2>
                    <pre class="p-4 bg-slate-900 text-emerald-400 rounded-xl font-mono text-xs overflow-x-auto">{{ $xmlContent }}</pre>
                </div>
            @endif
        </div>
    </div>
@endsection
