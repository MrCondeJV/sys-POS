@props([
    'status',
    'label' => null,
    'dot' => true,
    'size' => 'sm',
])

@php
    $rawStatus = $status;
    $enumLabel = null;

    if ($rawStatus instanceof \BackedEnum) {
        if (method_exists($rawStatus, 'label')) {
            $enumLabel = $rawStatus->label();
        }
        $rawStatus = $rawStatus->value;
    } elseif (is_bool($rawStatus)) {
        $rawStatus = $rawStatus ? 'ACTIVO' : 'INACTIVO';
    }

    $normalized = strtoupper(trim((string) $rawStatus));

    // Mapeo semántico completo para todos los Enums del proyecto (success, warning, danger, info, neutral)
    $variant = match ($normalized) {
        // Success (Verde)
        'COMPLETADA', 'PAGADA', 'ACTIVO', 'ACTIVA', 'ABIERTA', 'REGISTRADA', 'RECIBIDA', 'RECIBIDO',
        'APROBADA', 'PROCESADA', 'ACEPTADA', 'ACEPTADO', 'EMITIDO', 'DISPONIBLE', 'APLICADO', 'EXITOSO',
        'EN_STOCK', '1', 'TRUE' => 'success',

        // Warning (Ámbar)
        'PENDIENTE', 'POR_VENCER', 'PROXIMO_VENCER', 'EN_TRANSITO', 'SUSPENDIDO', 'SUSPENDIDA',
        'BAJO_STOCK', 'STOCK_BAJO' => 'warning',

        // Info (Azul Institucional)
        'PARCIAL', 'PRUEBA', 'ENVIADO' => 'info',

        // Danger (Rojo)
        'ANULADA', 'ANULADO', 'CANCELADA', 'CANCELADO', 'VENCIDA', 'VENCIDO', 'RECHAZADA', 'RECHAZADO',
        'FALLIDO', 'ERROR', 'AGOTADO', 'AGOTADA', 'SIN_STOCK', 'BLOQUEADO' => 'danger',

        // Neutral (Gris / Slate)
        'INACTIVO', 'INACTIVA', 'CERRADA', 'BORRADOR', 'ARCHIVADO', '0', 'FALSE' => 'neutral',

        default => 'neutral',
    };

    // Etiqueta legible por defecto si no se pasa $label ni viene de $enum->label()
    $displayLabel = $label ?? $enumLabel ?? match ($normalized) {
        'COMPLETADA' => 'Completada',
        'ANULADA' => 'Anulada',
        'ANULADO' => 'Anulado',
        'ACTIVO' => 'Activo',
        'ACTIVA' => 'Activa',
        'INACTIVO' => 'Inactivo',
        'INACTIVA' => 'Inactiva',
        'ABIERTA' => 'Abierta',
        'CERRADA' => 'Cerrada',
        'REGISTRADA' => 'Registrada',
        'RECIBIDA', 'RECIBIDO' => 'Recibido',
        'PENDIENTE' => 'Pendiente',
        'PARCIAL' => 'Parcial',
        'PAGADA' => 'Pagada',
        'PRUEBA' => 'Prueba',
        'SUSPENDIDO' => 'Suspendido',
        'SUSPENDIDA' => 'Suspendida',
        'VENCIDA' => 'Vencida',
        'VENCIDO' => 'Vencido',
        'CANCELADA' => 'Cancelada',
        'CANCELADO' => 'Cancelado',
        'ACEPTADA' => 'Aceptada',
        'ACEPTADO' => 'Aceptado',
        'RECHAZADA' => 'Rechazada',
        'RECHAZADO' => 'Rechazado',
        'EMITIDO' => 'Emitido',
        'DISPONIBLE' => 'Disponible',
        'APLICADO' => 'Aplicado',
        'AGOTADO' => 'Agotado',
        'AGOTADA' => 'Agotada',
        'PROXIMO_VENCER', 'POR_VENCER' => 'Próximo a vencer',
        'EN_TRANSITO' => 'En tránsito',
        'BORRADOR' => 'Borrador',
        default => ucfirst(strtolower(str_replace('_', ' ', $normalized))),
    };
@endphp

<x-badge :variant="$variant" :size="$size" :dot="$dot" {{ $attributes }}>
    {{ $displayLabel }}
</x-badge>
