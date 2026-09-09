<?php

namespace App\Actions\ListasPrecios;

use App\Enums\EstadoGeneral;
use App\Models\Cliente;
use App\Models\ListaPrecio;
use App\Models\Producto;
use App\Support\Tenancy\CompanyContext;

class ResolverPrecioProductoAction
{
    /**
     * Resuelve el precio aplicable de un producto considerando cliente, lista explícita o lista por defecto.
     */
    public function execute(Producto $producto, ?int $clienteId = null, ?int $listaPrecioId = null): float
    {
        $lista = null;

        // 1. Si se especificó una lista de precios puntual
        if ($listaPrecioId) {
            $lista = ListaPrecio::find($listaPrecioId);
        }

        // 2. Si no, y hay cliente, verificar si el cliente tiene una lista asignada
        if (! $lista && $clienteId) {
            $cliente = Cliente::find($clienteId);
            if ($cliente && $cliente->lista_precio_id) {
                $lista = $cliente->listaPrecio;
            }
        }

        // 3. Si no, buscar la lista predeterminada activa de la empresa
        if (! $lista) {
            $empresaId = CompanyContext::getId() ?? $producto->empresa_id;
            $lista = ListaPrecio::where('empresa_id', $empresaId)
                ->where('es_predeterminada', true)
                ->where('estado', EstadoGeneral::ACTIVO->value)
                ->first();
        }

        // 4. Si encontramos una lista activa, calculamos el precio
        if ($lista && $lista->estado === EstadoGeneral::ACTIVO) {
            return $lista->calcularPrecio($producto);
        }

        // 5. Fallback al precio de venta estándar del producto
        return (float) $producto->precio_venta;
    }
}
