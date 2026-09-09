<?php

namespace App\Http\Controllers;

use App\Actions\Ventas\RegistrarVentaAction;
use App\Enums\EstadoCaja;
use App\Enums\EstadoGeneral;
use App\Enums\EstadoSesionCaja;
use App\Enums\TipoComprobanteVenta;
use App\Enums\TipoPago;
use App\Models\Caja;
use App\Models\CajaSesion;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Inventario;
use App\Models\Producto;
use App\Support\Tenancy\BranchContext;
use App\Support\Tenancy\CompanyContext;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PosController extends Controller
{
    /**
     * Interfaz interactiva del Punto de Venta (POS).
     */
    public function index(Request $request): View
    {
        if (Gate::denies('create', \App\Models\Venta::class)) {
            abort(403, 'No tienes autorización para acceder a la terminal de punto de venta.');
        }

        $empresaId = CompanyContext::getId();
        $sucursalId = BranchContext::getId() ?? auth()->user()->sucursal_id;

        // Verificar si existe una caja abierta en la sucursal operada por el usuario o activa
        $sesionCaja = CajaSesion::where('empresa_id', $empresaId)
            ->where('sucursal_id', $sucursalId)
            ->where('estado', EstadoSesionCaja::ABIERTA->value)
            ->where(function ($q) {
                $q->where('user_id', auth()->id())
                  ->orWhereNull('user_id');
            })
            ->latest('fecha_apertura')
            ->first();

        // Si no hay sesión propia, buscar cualquier sesión abierta en la sucursal
        if (! $sesionCaja) {
            $sesionCaja = CajaSesion::where('empresa_id', $empresaId)
                ->where('sucursal_id', $sucursalId)
                ->where('estado', EstadoSesionCaja::ABIERTA->value)
                ->latest('fecha_apertura')
                ->first();
        }

        // Si no hay ninguna caja abierta en la sucursal, mostrar pantalla para apertura rápida
        if (! $sesionCaja) {
            $cajasDisponibles = Caja::where('empresa_id', $empresaId)
                ->where('sucursal_id', $sucursalId)
                ->where('estado', EstadoCaja::ACTIVA->value)
                ->get();

            return view('pos.sin_caja', compact('cajasDisponibles'));
        }

        // Cargar Catálogo de Productos con Existencias en la Sucursal
        $inventarios = Inventario::where('empresa_id', $empresaId)
            ->where('sucursal_id', $sucursalId)
            ->with(['producto.categoria', 'producto.unidadMedida'])
            ->get();

        $productos = $inventarios->map(function ($inv) {
            $p = $inv->producto;
            if (! $p || $p->estado !== EstadoGeneral::ACTIVO) {
                return null;
            }

            return [
                'id' => $p->id,
                'nombre' => $p->nombre,
                'codigo' => $p->codigo ?? '',
                'codigo_barras' => $p->codigo_barras ?? '',
                'sku' => $p->sku ?? '',
                'precio_venta' => (float) $p->precio_venta,
                'stock' => (float) $inv->stock,
                'categoria_id' => $p->categoria_id,
                'categoria_nombre' => $p->categoria?->nombre ?? 'General',
                'unidad' => $p->unidadMedida?->abreviatura ?? 'UND',
                'iva_porcentaje' => (float) ($p->impuesto_porcentaje ?? 0),
            ];
        })->filter()->values();

        // Clientes
        $clientes = Cliente::where('empresa_id', $empresaId)
            ->orderByDesc('es_predeterminado')
            ->orderBy('razon_social')
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'razon_social' => $c->razon_social,
                    'numero_documento' => $c->numero_documento,
                    'tipo_documento' => $c->tipo_documento->value,
                    'es_consumidor_final' => (bool) $c->es_predeterminado,
                    'tiene_credito' => $c->tieneCredito(),
                    'cupo_disponible' => (float) $c->cupoDisponible(),
                    'plazo_dias' => $c->plazo_dias,
                ];
            });

        // Categorías para filtrado rápido
        $categorias = Categoria::where('empresa_id', $empresaId)
            ->where('estado', EstadoGeneral::ACTIVO->value)
            ->orderBy('nombre')
            ->get();

        return view('pos.index', compact(
            'sesionCaja',
            'productos',
            'clientes',
            'categorias'
        ));
    }

    /**
     * Procesa la venta enviada desde el frontend POS (vía AJAX / JSON).
     */
    public function procesar(Request $request, RegistrarVentaAction $action): JsonResponse
    {
        if (Gate::denies('create', \App\Models\Venta::class)) {
            return response()->json(['error' => 'No tienes autorización para registrar ventas.'], 403);
        }

        $empresaId = CompanyContext::getId();
        $sucursalId = BranchContext::getId() ?? auth()->user()->sucursal_id;

        $validated = $request->validate([
            'caja_sesion_id' => ['required', 'exists:cajas_sesiones,id'],
            'cliente_id' => ['nullable', 'exists:clientes,id'],
            'tipo_pago' => ['required', 'in:CONTADO,CREDITO'],
            'metodo_pago' => ['required', 'string'],
            'tipo_comprobante' => ['nullable', 'in:TICKET,FACTURA,NOTA_VENTA'],
            'pago_con' => ['nullable', 'numeric', 'min:0'],
            'pagos' => ['nullable', 'array'],
            'pagos.*.metodo_pago' => ['required', 'string'],
            'pagos.*.monto' => ['required', 'numeric', 'min:0.01'],
            'pagos.*.referencia' => ['nullable', 'string', 'max:100'],
            'observaciones' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.producto_id' => ['required', 'exists:productos,id'],
            'items.*.cantidad' => ['required', 'numeric', 'min:0.01'],
            'items.*.precio_unitario' => ['required', 'numeric', 'min:0'],
            'items.*.descuento' => ['nullable', 'numeric', 'min:0'],
            'items.*.impuesto_porcentaje' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $tipoPago = TipoPago::from($validated['tipo_pago']);
            $tipoComprobante = isset($validated['tipo_comprobante'])
                ? TipoComprobanteVenta::from($validated['tipo_comprobante'])
                : TipoComprobanteVenta::TICKET;

            $venta = $action->execute(
                empresaId: $empresaId,
                sucursalId: $sucursalId,
                userId: auth()->id(),
                items: $validated['items'],
                clienteId: $validated['cliente_id'] ?? null,
                tipoPago: $tipoPago,
                metodoPago: $validated['metodo_pago'],
                tipoComprobante: $tipoComprobante,
                cajaSesionId: (int) $validated['caja_sesion_id'],
                pagoCon: isset($validated['pago_con']) ? (float) $validated['pago_con'] : null,
                pagos: $validated['pagos'] ?? null,
                observaciones: $validated['observaciones'] ?? null
            );

            return response()->json([
                'success' => true,
                'venta_id' => $venta->id,
                'numero_venta' => $venta->numero_venta,
                'total' => (float) $venta->total,
                'cambio' => (float) $venta->cambio,
                'ticket_url' => route('ventas.ticket', $venta),
                'show_url' => route('ventas.show', $venta),
                'message' => "¡Venta {$venta->numero_venta} registrada con éxito!",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
