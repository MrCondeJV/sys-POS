@extends('layouts.app')

@section('title', 'Pagar Suscripción con Wompi')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    {{-- Migas de pan / Volver --}}
    <div class="flex items-center gap-2 text-sm text-text-muted">
        <a href="{{ route('suscripcion.index') }}" class="hover:text-text-primary transition">← Volver a Suscripción</a>
    </div>

    {{-- Tarjeta de Resumen de Pago --}}
    <div class="bg-surface rounded-xl border border-border p-6 space-y-6">
        <div class="border-b border-border pb-4">
            <h1 class="text-xl font-bold text-text-primary">Confirmar y Pagar Suscripción</h1>
            <p class="text-sm text-text-muted mt-1">Transacción segura procesada en línea a través de Wompi Colombia (Bancolombia).</p>
        </div>

        <div class="space-y-3">
            <div class="flex justify-between text-sm">
                <span class="text-text-muted">Empresa / Comercio:</span>
                <span class="font-semibold text-text-primary">{{ $empresa->nombre_comercial }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-text-muted">Plan Seleccionado:</span>
                <span class="font-semibold text-text-primary">{{ $plan->nombre }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-text-muted">Ciclo de Facturación:</span>
                <span class="font-semibold text-text-primary">{{ $pago->ciclo_facturacion }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-text-muted">Referencia Única:</span>
                <span class="font-mono text-xs text-text-primary">{{ $pago->reference }}</span>
            </div>
            <div class="border-t border-border pt-3 flex justify-between items-baseline">
                <span class="text-base font-bold text-text-primary">Total a Pagar:</span>
                <div class="text-right">
                    <span class="text-2xl font-extrabold text-brand-600 tabular-nums">${{ number_format($pago->amount_in_cents / 100, 0, ',', '.') }}</span>
                    <span class="text-xs text-text-muted font-bold ml-1">COP</span>
                </div>
            </div>
        </div>

        {{-- Widget Oficial de Wompi Checkout --}}
        <div class="border-t border-border pt-6 flex flex-col items-center justify-center space-y-4">
            <div class="text-center text-xs text-text-muted mb-2">
                Acepta tarjetas de crédito/débito (Visa, Mastercard, AMEX), Nequi, PSE, transferencias y QR Bancolombia.
            </div>

            <form action="{{ route('suscripcion.retorno') }}" method="GET">
                <script
                    src="https://checkout.wompi.co/widget.js"
                    data-render="button"
                    data-public-key="{{ $wompiConfig['publicKey'] }}"
                    data-currency="{{ $wompiConfig['currency'] }}"
                    data-amount-in-cents="{{ $wompiConfig['amountInCents'] }}"
                    data-reference="{{ $wompiConfig['reference'] }}"
                    data-signature:integrity="{{ $wompiConfig['signature'] }}"
                    data-redirect-url="{{ $wompiConfig['redirectUrl'] }}"
                ></script>
            </form>

            <div class="flex items-center gap-2 text-xs text-text-muted mt-4">
                <svg class="w-4 h-4 text-success-text" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <span>Conexión cifrada TLS 1.3 con certificación de cumplimiento PCI-DSS.</span>
            </div>
        </div>
    </div>
</div>
@endsection
