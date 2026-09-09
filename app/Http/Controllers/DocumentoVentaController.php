<?php

namespace App\Http\Controllers;

use App\Actions\Auditoria\RegistrarAuditoriaAction;
use App\Enums\EstadoDocumentoVenta;
use App\Enums\TipoDocumentoVenta;
use App\Models\DocumentoVenta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DocumentoVentaController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', DocumentoVenta::class);

        $query = DocumentoVenta::with(['cliente', 'sucursal', 'usuario', 'venta'])
            ->latest('fecha_emision');

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_emision', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_emision', '<=', $request->fecha_hasta);
        }

        if ($request->filled('q')) {
            $search = trim($request->q);
            $query->where(function ($q) use ($search) {
                $q->where('numero_completo', 'like', "%{$search}%")
                  ->orWhereHas('cliente', fn ($c) => $c->where('razon_social', 'like', "%{$search}%")->orWhere('numero_documento', 'like', "%{$search}%"));
            });
        }

        $documentos = $query->paginate(20)->withQueryString();

        $tipos = TipoDocumentoVenta::cases();
        $estados = EstadoDocumentoVenta::cases();

        return view('documentos.index', compact('documentos', 'tipos', 'estados'));
    }

    public function show(DocumentoVenta $documento): View
    {
        Gate::authorize('view', $documento);

        $documento->load([
            'empresa',
            'sucursal',
            'cliente',
            'usuario',
            'venta.detalles.producto',
            'venta.pagos',
        ]);

        return view('documentos.show', compact('documento'));
    }

    public function anular(Request $request, DocumentoVenta $documento): RedirectResponse
    {
        Gate::authorize('anular', $documento);

        $request->validate([
            'motivo' => ['required', 'string', 'max:255'],
        ]);

        $motivo = trim($request->motivo);

        $documento->update([
            'estado' => EstadoDocumentoVenta::ANULADO,
            'observaciones' => trim(($documento->observaciones ?? '') . " [ANULADO MANUALMENTE: {$motivo}]"),
        ]);

        RegistrarAuditoriaAction::execute(
            accion: 'ANULAR_DOCUMENTO_VENTA_MANUAL',
            modulo: 'DOCUMENTOS',
            model: $documento,
            datosAnteriores: ['estado' => EstadoDocumentoVenta::EMITIDO->value],
            datosNuevos: ['estado' => EstadoDocumentoVenta::ANULADO->value],
            descripcion: "Documento {$documento->numero_completo} anulado manualmente. Motivo: {$motivo}",
            usuario: $request->user(),
            empresaId: $documento->empresa_id
        );

        return redirect()->route('documentos.show', $documento)
            ->with('success', "Documento {$documento->numero_completo} ha sido marcado como anulado.");
    }
}
