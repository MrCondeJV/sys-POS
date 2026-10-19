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

    // Determine semantic variant (success, warning, danger, info, neutral)
    $variant = match ($normalized) {
        // Success (Verde)
        'COMPLETADA', 'PAGADA', 'ACTIVO', 'ACTIVA', 'ABIERTA', 'REGISTRADA', 'RECIBIDA', 'RECIBIDO', 'APROBADA', 'PROCESADA', 'ACEPTADA', 'EXITOSO', '1', 'TRUE' => 'success',

        // Warning (Ámbar)
        'PENDIENTE', 'POR_VENCER', 'EN_TRANSITO', 'SUSPENDIDO', 'SUSPENDIDA' => 'warning',

        // Info (Azul)
        'PARCIAL', 'PRUEBA', 'ENVIADO' => 'info',

        // Danger (Rojo)
        'ANULADA', 'CANCELADA', 'VENCIDA', 'RECHAZADA', 'FALLIDO', 'AGOTADO', 'BLOQUEADO' => 'danger',

        // Neutral (Gris / Slate)
        'INACTIVO', 'INACTIVA', 'CERRADA', 'BORRADOR', 'ARCHIVADO', '0', 'FALSE' => 'neutral',

        default => 'neutral',
    };

    // Determine default display label if not explicitly passed
    $displayLabel = $label ?? $enumLabel ?? match ($normalized) {
        'COMPLETADA' => 'Completada',
        'ANULADA' => 'Anulada',
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
        'CANCELADA' => 'Cancelada',
        'ACEPTADA' => 'Aceptada',
        'RECHAZADA' => 'Rechazada',
        'EN_TRANSITO' => 'En Tránsito',
        'BORRADOR' => 'Borrador',
        default => ucfirst(strtolower(str_replace('_', ' ', $normalized))),
    };
@endphp

<x-badge :variant="$variant" :size="$size" :dot="$dot" {{ $attributes }}>
    {{ $displayLabel }}
</x-badge>
