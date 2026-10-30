@extends('layouts.app')

@section('title', 'Crédito y Cartera')

@section('content')
<div class="space-y-6" x-data="{
    abonoModalOpen: false,
    urlAbonoTemplate: '{{ route('cartera.abono.store', '__CUENTA__') }}',
    cuentaSeleccionada: {
        id: null,
        numero: '',
        cliente: '',
        saldo: 0,
        urlAbono: ''
    },
    abrirModalAbono(cuenta) {
        this.cuentaSeleccionada = {
            id: cuenta.id,
            numero: cuenta.numero_documento,
            cliente: cuenta.cliente ? cuenta.cliente.razon_social : 'Cliente',
            saldo: parseFloat(cuenta.saldo_pendiente),
            urlAbono: this.urlAbonoTemplate.replace('__CUENTA__', cuenta.id)
        };
        this.abonoModalOpen = true;
    }
}">

    <!-- Encabezado & Acciones -->
    <x-page-header
        title="Crédito y Cartera"
        description="Registro de ventas fiadas, abonos de los clientes y estados de cuenta."
        :breadcrumbs="[
            ['label' => 'Crédito y Cartera']
        ]">
        <x-slot:actions>
            <x-button variant="primary" href="{{ route('cartera.create') }}">
                <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nueva Cuenta por Cobrar
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <!-- Mensajes Flash de Éxito -->
    @if(session('success'))
        <div class="p-4 rounded-lg bg-success-bg border border-success-border text-success-text flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <svg class="h-5 w-5 text-success-text flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm font-semibold">{{ session('success') }}</span>
            </div>
            @if(session('recibo_id'))
                <x-button variant="secondary" size="sm" href="{{ route('cartera.recibo', session('recibo_id')) }}" target="_blank">
                    <svg class="h-4 w-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Imprimir Recibo de Caja
                </x-button>
            @endif
        </div>
    @endif

    <!-- Tarjetas de Métricas Rápidas (KPIs) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-kpi-card
            title="Cartera Total por Cobrar"
            :value="'$' . number_format($totalCartera, 2)"
            subtitle="Saldo pendiente acumulado"
            icon="credit-card"
        />
        <x-kpi-card
            title="Cuentas Abiertas"
            :value="number_format($cuentasAbiertas)"
            subtitle="Ventas fiadas con saldo"
            icon="coins"
        />
        <x-kpi-card
            title="Clientes con Deuda"
            :value="number_format($clientesConDeuda)"
            subtitle="Clientes que deben actualmente"
            icon="users"
        />
        <x-kpi-card
            title="Recaudo de Cartera (Mes)"
            :value="'$' . number_format($totalRecaudadoMes, 2)"
            :subtitle="'Abonos en ' . now()->translatedFormat('F')"
            icon="currency-dollar"
        />
    </div>

    <!-- Barra de Filtros y Búsqueda -->
    <x-filter-bar :action="route('cartera.index')" :clear-url="route('cartera.index')" :has-active-filters="!empty($term) || !empty($estado) || !empty($clienteId)">
        <div class="sm:col-span-12 lg:col-span-5 relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-text-muted">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input type="text" name="buscar" value="{{ $term ?? '' }}"
                placeholder="Buscar por documento, concepto o cliente..."
                class="input pl-9">
        </div>

        <div class="sm:col-span-6 lg:col-span-3">
            <select name="cliente_id" class="input">
                <option value="">Todos los clientes</option>
                @foreach($clientes as $c)
                    <option value="{{ $c->id }}" @selected(($clienteId ?? '') == $c->id)>
                        {{ $c->razon_social }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="sm:col-span-6 lg:col-span-2">
            <select name="estado" class="input">
                <option value="">Todos los estados</option>
                <option value="PENDIENTES" @selected(($estado ?? '') === 'PENDIENTES')>Todas Pendientes (Con saldo)</option>
                <option value="VENCIDA" @selected(($estado ?? '') === 'VENCIDA')>Pasó la fecha acordada</option>
                <option value="PAGADA" @selected(($estado ?? '') === 'PAGADA')>Totalmente Pagadas</option>
            </select>
        </div>
    </x-filter-bar>

    <!-- Tabla Principal de Cuentas por Cobrar (Desktop) -->
    <x-table class="hidden lg:block">
        <x-slot:head>
            <tr>
                <th class="px-4 py-3 text-left">Documento / Concepto</th>
                <th class="px-4 py-3 text-left">Cliente Deudor</th>
                <th class="px-4 py-3 text-right">Deuda & Saldo</th>
                <th class="hidden @4xl:table-cell px-4 py-3 text-left">Antigüedad / Acuerdo</th>
                <th class="px-4 py-3 text-center">Estado</th>
                <th class="px-4 py-3 text-right">Acciones</th>
            </tr>
        </x-slot:head>

        @forelse($cuentas as $cuenta)
            <tr class="hover:bg-slate-50/60 transition">
                <td class="px-4 py-3">
                    <div class="flex items-center space-x-3">
                        <div class="h-9 w-9 flex-shrink-0 rounded-lg flex items-center justify-center font-bold text-xs bg-brand-50 text-brand-700 border border-brand-200">
                            CXC
                        </div>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-x-2">
                                <a href="{{ route('cartera.show', $cuenta) }}" class="font-semibold text-brand-600 hover:text-brand-800 text-sm">
                                    {{ $cuenta->numero_documento }}
                                </a>
                                <span class="text-xs text-slate-400">({{ $cuenta->fecha_emision->format('d/m/Y') }})</span>
                            </div>
                            <span class="text-xs text-slate-600 font-medium line-clamp-1">
                                {{ $cuenta->concepto }}
                            </span>
                            @if($cuenta->fecha_vencimiento)
                                <span class="@4xl:hidden text-[11px] font-medium block {{ $cuenta->estaVencida() ? 'text-danger-text' : 'text-slate-500' }}">
                                    Acordado {{ $cuenta->fecha_vencimiento->format('d/m/Y') }}
                                </span>
                            @endif
                            <span class="text-[11px] text-slate-400 block">
                                {{ $cuenta->sucursal?->nombre ?? 'Sede Central' }}
                            </span>
                        </div>
                    </div>
                </td>

                <td class="px-4 py-3">
                    <div class="min-w-0">
                        <a href="{{ route('cartera.estado-cuenta', $cuenta->cliente) }}"
                            class="text-sm font-semibold text-slate-900 hover:text-brand-600 transition line-clamp-2"
                            title="Ver estado de cuenta de {{ $cuenta->cliente->razon_social }}">
                            {{ $cuenta->cliente->razon_social }}
                        </a>
                        <span class="text-xs text-slate-500 font-mono block">
                            {{ $cuenta->cliente->tipo_documento->value }} {{ $cuenta->cliente->numero_documento }}
                        </span>
                        <span class="text-[11px] text-slate-400 block">
                            {{ $cuenta->cliente->telefono ?? 'Sin teléfono' }}
                        </span>
                    </div>
                </td>

                <td class="px-4 py-3 whitespace-nowrap text-right">
                    <div class="text-sm font-semibold font-mono tabular-nums {{ (float) $cuenta->saldo_pendiente > 0 ? 'text-slate-900' : 'text-slate-400' }}">
                        ${{ number_format($cuenta->saldo_pendiente, 2) }}
                        <span class="block text-xs font-normal text-slate-400">de ${{ number_format($cuenta->monto_total, 2) }}</span>
                    </div>
                    <div class="w-28 ml-auto mt-1.5">
                        <div class="flex justify-between text-[10px] text-slate-500 font-semibold mb-0.5">
                            <span>{{ $cuenta->porcentajePagado() }}% pagado</span>
                        </div>
                        <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full {{ $cuenta->estado->value === 'PAGADA' ? 'bg-success-text' : ($cuenta->estaVencida() ? 'bg-danger-text' : 'bg-brand-600') }}"
                                 style="width: {{ $cuenta->porcentajePagado() }}%"></div>
                        </div>
                    </div>
                </td>

                <td class="hidden @4xl:table-cell px-4 py-3 whitespace-nowrap">
                    @if($cuenta->estado->value === 'PAGADA')
                        <span class="text-xs text-success-text font-medium">Cancelada</span>
                    @else
                        <div class="text-sm font-semibold text-slate-800">
                            {{ $cuenta->diasAntiguedad() }} {{ $cuenta->diasAntiguedad() === 1 ? 'día' : 'días' }} fiado
                        </div>
                        @if($cuenta->estaVencida())
                            <span class="text-xs font-semibold text-danger-text">
                                Pasó la fecha acordada ({{ $cuenta->fecha_vencimiento->format('d/m/Y') }})
                            </span>
                        @elseif($cuenta->fecha_vencimiento)
                            <span class="text-xs text-slate-500">Acordado para el {{ $cuenta->fecha_vencimiento->format('d/m/Y') }}</span>
                        @else
                            <span class="text-xs text-slate-400">Sin fecha acordada</span>
                        @endif
                    @endif
                </td>

                <td class="px-4 py-3 whitespace-nowrap text-center">
                    <x-status-badge :status="$cuenta->estado->value" />
                </td>

                <td class="px-4 py-3 whitespace-nowrap text-right text-sm">
                    <div class="flex items-center justify-end space-x-1.5">
                        @if((float) $cuenta->saldo_pendiente > 0 && $cuenta->estado->value !== 'ANULADA')
                            <x-button size="sm" variant="primary" @click="abrirModalAbono({{ json_encode($cuenta) }})" type="button" title="Registrar Abono">
                                <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Abonar
                            </x-button>
                        @endif

                        <x-icon-button
                            href="{{ route('cartera.show', $cuenta) }}"
                            variant="secondary"
                            icon="eye"
                            title="Ver detalle de la cuenta"
                        />

                        <x-icon-button
                            href="{{ route('cartera.estado-cuenta', $cuenta->cliente) }}"
                            variant="secondary"
                            icon="document"
                            title="Ver estado de cuenta completo del cliente"
                        />
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-4 py-8">
                    <x-empty-state
                        title="No hay cuentas por cobrar"
                        description="No se encontraron cuentas con los filtros o términos de búsqueda seleccionados."
                    >
                        <x-slot:actions>
                            <x-button variant="primary" href="{{ route('cartera.create') }}">
                                Registrar Cuenta por Cobrar
                            </x-button>
                        </x-slot:actions>
                    </x-empty-state>
                </td>
            </tr>
        @endforelse
    </x-table>

    <!-- Vista Móvil y Tablet -->
    <div class="lg:hidden bg-white rounded-xl border border-slate-200/80 shadow-xs divide-y divide-slate-100 overflow-hidden">
        @forelse($cuentas as $cuenta)
            <div class="p-4 space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <a href="{{ route('cartera.show', $cuenta) }}" class="font-semibold text-brand-600 hover:text-brand-800 text-sm">
                            {{ $cuenta->numero_documento }}
                        </a>
                        <div class="text-xs text-slate-600 font-medium line-clamp-2">{{ $cuenta->concepto }}</div>
                    </div>
                    <x-status-badge :status="$cuenta->estado->value" />
                </div>

                <div class="text-xs text-slate-600 space-y-1">
                    <div>
                        <span class="text-slate-400">Cliente:</span>
                        <a href="{{ route('cartera.estado-cuenta', $cuenta->cliente) }}" class="font-semibold text-slate-900 hover:text-brand-600">
                            {{ $cuenta->cliente->razon_social }}
                        </a>
                    </div>
                    <div><span class="text-slate-400">Emisión:</span> {{ $cuenta->fecha_emision->format('d/m/Y') }}</div>
                    @if($cuenta->fecha_vencimiento)
                        <div>
                            <span class="text-slate-400">Acordado:</span>
                            <span class="font-semibold text-slate-800">{{ $cuenta->fecha_vencimiento->format('d/m/Y') }}</span>
                            @if($cuenta->estaVencida())
                                <span class="font-semibold text-danger-text">&bull; Pasó la fecha acordada</span>
                            @endif
                        </div>
                    @endif
                </div>

                <div>
                    <div class="flex justify-between text-[10px] text-slate-500 font-semibold mb-0.5">
                        <span>{{ $cuenta->porcentajePagado() }}% pagado</span>
                        <span class="tabular-nums">de ${{ number_format($cuenta->monto_total, 2) }}</span>
                    </div>
                    <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full {{ $cuenta->estado->value === 'PAGADA' ? 'bg-success-text' : ($cuenta->estaVencida() ? 'bg-danger-text' : 'bg-brand-600') }}"
                             style="width: {{ $cuenta->porcentajePagado() }}%"></div>
                    </div>
                </div>

                <div class="pt-2 flex items-center justify-between gap-3 border-t border-slate-100">
                    <div>
                        <span class="text-[10px] text-slate-400 uppercase font-semibold block">Saldo</span>
                        <span class="font-bold font-mono text-base tabular-nums {{ (float) $cuenta->saldo_pendiente > 0 ? 'text-slate-900' : 'text-slate-400' }}">
                            ${{ number_format($cuenta->saldo_pendiente, 2) }}
                        </span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        @if((float) $cuenta->saldo_pendiente > 0 && $cuenta->estado->value !== 'ANULADA')
                            <x-button size="sm" variant="primary" @click="abrirModalAbono({{ json_encode($cuenta) }})" type="button">
                                Abonar
                            </x-button>
                        @endif
                        <x-icon-button href="{{ route('cartera.show', $cuenta) }}" variant="secondary" icon="eye" title="Ver detalle de la cuenta" />
                        <x-icon-button href="{{ route('cartera.estado-cuenta', $cuenta->cliente) }}" variant="secondary" icon="document" title="Ver estado de cuenta completo del cliente" />
                    </div>
                </div>
            </div>
        @empty
            <x-empty-state
                title="No hay cuentas por cobrar"
                description="No se encontraron cuentas con los filtros o términos de búsqueda seleccionados."
            />
        @endforelse
    </div>

    @if($cuentas->hasPages())
        <div class="p-4 border-t border-slate-200 bg-slate-50/50 rounded-xl">
            {{ $cuentas->links() }}
        </div>
    @endif

    <!-- Modal Reactivo de Abono Rápido (Alpine.js) -->
    <div x-cloak x-show="abonoModalOpen" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
            <div x-show="abonoModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs"
                 @click="abonoModalOpen = false"></div>

            <div x-show="abonoModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full border border-slate-200 p-6 space-y-4">

                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-bold text-slate-900" x-text="'Registrar Abono: ' + cuentaSeleccionada.numero"></h3>
                        <p class="text-xs text-slate-500" x-text="cuentaSeleccionada.cliente"></p>
                    </div>
                    <button type="button" @click="abonoModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Saldo Pendiente Banner -->
                <div class="p-3.5 rounded-lg bg-brand-50 border border-brand-100 flex items-center justify-between">
                    <span class="text-xs font-semibold text-brand-900">Saldo Pendiente Actual:</span>
                    <span class="text-base font-bold font-mono tabular-nums text-brand-700" x-text="'$' + cuentaSeleccionada.saldo.toLocaleString('es-CO', {minimumFractionDigits: 2})"></span>
                </div>

                <form :action="cuentaSeleccionada.urlAbono" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                            Monto a Abonar ($ COP) <span class="text-danger-text">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 font-semibold text-sm">$</span>
                            <input type="number" step="0.01" min="0.01" :max="cuentaSeleccionada.saldo" name="monto" :value="cuentaSeleccionada.saldo" required
                                class="block w-full pl-7 pr-3 py-2 border border-slate-300 rounded-lg text-sm font-semibold font-mono tabular-nums text-slate-900 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition shadow-xs">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                                Método de Pago <span class="text-danger-text">*</span>
                            </label>
                            <select name="metodo_pago" required class="input">
                                @foreach(\App\Enums\MetodoPagoCartera::cases() as $mp)
                                    <option value="{{ $mp->value }}">{{ $mp->label() }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                                Fecha de Pago <span class="text-danger-text">*</span>
                            </label>
                            <input type="date" name="fecha_pago" value="{{ now()->toDateString() }}" required
                                class="block w-full py-2 px-3 border border-slate-300 rounded-lg text-sm text-slate-700 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition shadow-xs">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                            Referencia / N° Comprobante <span class="text-slate-400 text-[11px] font-normal">(Opcional)</span>
                        </label>
                        <input type="text" name="referencia_pago" placeholder="Ej: TR-192834 ó Nequi 310..."
                            class="block w-full py-2 px-3 border border-slate-300 rounded-lg text-sm placeholder-slate-400 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition shadow-xs">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                            Notas / Concepto de Abono <span class="text-slate-400 text-[11px] font-normal">(Opcional)</span>
                        </label>
                        <input type="text" name="notas" placeholder="Ej: Abono parcial acuerdo de pago..."
                            class="block w-full py-2 px-3 border border-slate-300 rounded-lg text-sm placeholder-slate-400 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition shadow-xs">
                    </div>

                    <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                        <x-button type="button" variant="secondary" @click="abonoModalOpen = false">
                            Cancelar
                        </x-button>
                        <x-button type="submit" variant="primary">
                            Confirmar & Aplicar Abono
                        </x-button>
                    </div>
                </form>

            </div>
        </div>
    </div>

</div>
@endsection
