<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de Caja {{ $pago->numero_recibo }} - {{ $pago->cliente->razon_social }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
                color: black !important;
                padding: 0 !important;
            }
            .print-container {
                box-shadow: none !important;
                border: none !important;
                max-width: 100% !important;
                padding: 0 !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen py-8 text-slate-800 antialiased font-sans">

    <!-- Barra Superior de Control (Solo pantalla) -->
    <div class="max-w-3xl mx-auto mb-6 px-4 no-print flex items-center justify-between">
        <a href="{{ route('cartera.show', $pago->cuentaPorCobrar) }}"
            class="inline-flex items-center px-4 py-2 rounded-xl border border-slate-300 text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 transition shadow-sm">
            <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver a la Cuenta
        </a>

        <div class="flex items-center gap-3">
            <button onclick="window.print()"
                class="inline-flex items-center px-5 py-2 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Imprimir Recibo (Ctrl + P)
            </button>
        </div>
    </div>

    <!-- Contenedor del Recibo -->
    <div class="max-w-3xl mx-auto bg-white rounded-3xl shadow-md border border-slate-200 p-8 sm:p-12 print-container space-y-6">

        <!-- Encabezado de Empresa y Documento -->
        <div class="flex flex-col sm:flex-row justify-between items-start pb-6 border-b border-slate-200 gap-4">
            <div>
                <h1 class="text-xl font-black text-slate-900 tracking-tight">
                    {{ $pago->empresa->nombre_comercial ?? $pago->empresa->razon_social }}
                </h1>
                <p class="text-xs text-slate-500 font-semibold mt-0.5">
                    NIT: {{ $pago->empresa->nit }}{{ $pago->empresa->dv ? '-' . $pago->empresa->dv : '' }}
                </p>
                <p class="text-xs text-slate-500">
                    {{ $pago->empresa->direccion ?? '' }} {{ $pago->empresa->ciudad ? '• ' . $pago->empresa->ciudad : '' }}
                </p>
                <p class="text-xs text-slate-500">
                    Tel: {{ $pago->empresa->telefono ?? 'N/A' }} • {{ $pago->empresa->email ?? '' }}
                </p>
            </div>

            <div class="sm:text-right border-l-2 sm:border-l-0 border-indigo-600 pl-3 sm:pl-0">
                <div class="text-xs font-black text-indigo-600 uppercase tracking-widest">Recibo de Caja / Abono</div>
                <div class="text-2xl font-black text-slate-900 tracking-tight mt-0.5">
                    {{ $pago->numero_recibo }}
                </div>
                <div class="text-xs text-slate-500 mt-1">
                    Fecha: <span class="font-bold text-slate-800">{{ $pago->fecha_pago->format('d/m/Y') }}</span>
                </div>
                @if($pago->isAnulado())
                    <span class="inline-block mt-2 px-3 py-1 rounded-full text-xs font-black bg-rose-100 text-rose-800 border border-rose-300">
                        RECIBO ANULADO
                    </span>
                @endif
            </div>
        </div>

        <!-- Ficha del Cliente -->
        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div>
                <span class="text-slate-400 font-bold uppercase tracking-wider block mb-1">Recibido de:</span>
                <div class="text-sm font-black text-slate-900">{{ $pago->cliente->razon_social }}</div>
                <div class="text-slate-600 font-semibold mt-0.5">
                    {{ $pago->cliente->tipo_documento->value }}: {{ $pago->cliente->numero_documento }}
                </div>
                <div class="text-slate-500 mt-0.5">{{ $pago->cliente->direccion ?? 'Sin dirección' }}</div>
            </div>

            <div class="sm:text-right">
                <span class="text-slate-400 font-bold uppercase tracking-wider block mb-1">Detalles de Operación:</span>
                <div class="text-slate-700 font-semibold">
                    Medio de Pago: <span class="font-bold text-slate-900">{{ $pago->metodo_pago->label() }}</span>
                </div>
                @if($pago->referencia_pago)
                    <div class="text-slate-600 mt-0.5">Referencia: {{ $pago->referencia_pago }}</div>
                @endif
                <div class="text-slate-500 mt-0.5">Sucursal: {{ $pago->sucursal?->nombre ?? 'Principal' }}</div>
                <div class="text-slate-400 mt-0.5">Atendido por: {{ $pago->usuario?->name ?? 'Cajero' }}</div>
            </div>
        </div>

        <!-- Detalle de la Cuenta Aplicada -->
        <div class="border border-slate-200 rounded-2xl overflow-hidden">
            <table class="min-w-full divide-y divide-slate-200 text-xs">
                <thead class="bg-slate-100/70 font-bold text-slate-700">
                    <tr>
                        <th class="px-4 py-3 text-left">Documento de Cartera</th>
                        <th class="px-4 py-3 text-left">Concepto</th>
                        <th class="px-4 py-3 text-right">Saldo Anterior</th>
                        <th class="px-4 py-3 text-right">Monto Abonado</th>
                        <th class="px-4 py-3 text-right">Nuevo Saldo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr>
                        <td class="px-4 py-3 font-bold text-slate-900">
                            {{ $pago->cuentaPorCobrar->numero_documento }}
                        </td>
                        <td class="px-4 py-3 text-slate-700">
                            {{ $pago->cuentaPorCobrar->concepto }}
                        </td>
                        <td class="px-4 py-3 text-right font-medium text-slate-600">
                            ${{ number_format($pago->saldo_anterior, 2) }}
                        </td>
                        <td class="px-4 py-3 text-right font-black text-emerald-600 text-sm">
                            ${{ number_format($pago->monto, 2) }}
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-slate-900">
                            ${{ number_format($pago->saldo_posterior, 2) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Total en letras o notas -->
        <div class="p-4 rounded-2xl bg-indigo-50/60 border border-indigo-100 flex items-center justify-between">
            <div class="text-xs text-indigo-950">
                <span class="font-bold">Total Abonado en Caja:</span>
                @if($pago->notas)
                    <p class="text-[11px] text-indigo-700 mt-0.5 italic">Nota: {{ $pago->notas }}</p>
                @endif
            </div>
            <div class="text-2xl font-black text-indigo-700">
                ${{ number_format($pago->monto, 2) }} COP
            </div>
        </div>

        <!-- Firmas de Aceptación -->
        <div class="grid grid-cols-2 gap-12 pt-12 text-xs">
            <div class="text-center border-t border-slate-300 pt-2">
                <p class="font-bold text-slate-800">{{ $pago->usuario?->name ?? 'Cajero Responsable' }}</p>
                <p class="text-[11px] text-slate-400">Recaudado / Entregado en Caja</p>
            </div>
            <div class="text-center border-t border-slate-300 pt-2">
                <p class="font-bold text-slate-800">{{ $pago->cliente->razon_social }}</p>
                <p class="text-[11px] text-slate-400">Firma / Sello de Conformidad</p>
            </div>
        </div>

        <!-- Pie de página legal -->
        <div class="pt-6 border-t border-slate-100 text-center text-[10px] text-slate-400">
            Documento de control administrativo interno de tesorería y recaudo de cartera. Generado por Sys-POS.
        </div>

    </div>

    @if(request()->boolean('print'))
    <script>
        window.addEventListener('DOMContentLoaded', function () {
            setTimeout(function () {
                window.print();
            }, 350);
        });
    </script>
    @endif
</body>
</html>
