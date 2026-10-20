@extends('layouts.app')

@section('title', 'Estado de la Transacción Wompi')

@section('content')
<div class="max-w-xl mx-auto space-y-6 text-center">
    <div class="bg-surface rounded-xl border border-border p-8 space-y-6">
        @if($pago && $pago->isApproved())
            <div class="w-16 h-16 rounded-full bg-success-bg text-success-text mx-auto flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-text-primary">¡Pago Aprobado con Éxito!</h1>
            <p class="text-sm text-text-muted">Tu suscripción ha sido renovada. Ya puedes disfrutar de todas las funcionalidades operativas de tu plan.</p>
        @else
            <div class="w-16 h-16 rounded-full bg-brand-50 text-brand-600 mx-auto flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-text-primary">Transacción en Verificación</h1>
            <p class="text-sm text-text-muted">Estamos esperando la confirmación de la entidad bancaria a través del webhook de Wompi. Esto suele tardar entre unos segundos y un par de minutos según el método de pago seleccionado.</p>
        @endif

        @if($wompiTxId)
            <div class="p-4 rounded-lg bg-surface-muted border border-border text-xs text-text-muted text-left space-y-1 font-mono">
                <p><strong>ID de Transacción Wompi:</strong> {{ $wompiTxId }}</p>
                @if($pago)
                    <p><strong>Referencia Local:</strong> {{ $pago->reference }}</p>
                    <p><strong>Monto:</strong> ${{ number_format($pago->amount_in_cents / 100, 0, ',', '.') }} COP</p>
                @endif
            </div>
        @endif

        <div class="pt-4 flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('suscripcion.index') }}" class="inline-flex justify-center items-center px-4 py-2.5 text-sm font-semibold rounded-lg bg-brand-600 text-white hover:bg-brand-700 transition">
                Ir a Mi Suscripción
            </a>
            <a href="{{ route('dashboard') }}" class="inline-flex justify-center items-center px-4 py-2.5 text-sm font-medium rounded-lg border border-border text-text-primary hover:bg-surface-muted transition">
                Ir al Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
