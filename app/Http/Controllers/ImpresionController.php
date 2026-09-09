<?php

namespace App\Http\Controllers;

use App\Enums\FormatoImpresion;
use App\Models\AbonoCartera;
use App\Models\CajaSesion;
use App\Models\DocumentoVenta;
use App\Models\Venta;
use App\Services\ImpresionService;
use App\Support\Tenancy\CompanyContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ImpresionController extends Controller
{
    public function __construct(
        protected ImpresionService $impresionService
    ) {}

    /**
     * Imprime un documento comercial formal (Factura, Ticket, Documento Equivalente).
     */
    public function imprimirDocumento(Request $request, DocumentoVenta $documento): View
    {
        Gate::authorize('view', $documento);

        $formato = FormatoImpresion::tryFrom($request->query('formato', '80mm')) ?? FormatoImpresion::TICKET_80MM;

        return $this->impresionService->renderDocumento($documento, $formato);
    }

    /**
     * Imprime el comprobante de una venta directa.
     */
    public function imprimirVenta(Request $request, Venta $venta): View
    {
        if (CompanyContext::getId() && CompanyContext::getId() !== $venta->empresa_id) {
            abort(404);
        }

        Gate::authorize('view', $venta);

        $formato = FormatoImpresion::tryFrom($request->query('formato', '80mm')) ?? FormatoImpresion::TICKET_80MM;

        return $this->impresionService->renderVenta($venta, $formato);
    }

    /**
     * Imprime el comprobante de arqueo y cierre de sesión de caja.
     */
    public function imprimirCajaSesion(Request $request, CajaSesion $sesion): View
    {
        if (CompanyContext::getId() && CompanyContext::getId() !== $sesion->empresa_id) {
            abort(404);
        }

        $formato = FormatoImpresion::tryFrom($request->query('formato', '80mm')) ?? FormatoImpresion::TICKET_80MM;

        return $this->impresionService->renderCajaCierre($sesion, $formato);
    }

    /**
     * Imprime el recibo de abono / pago de cartera.
     */
    public function imprimirAbono(Request $request, \App\Models\PagoCliente $abono): View
    {
        if (CompanyContext::getId() && CompanyContext::getId() !== $abono->empresa_id) {
            abort(404);
        }

        $formato = FormatoImpresion::tryFrom($request->query('formato', '80mm')) ?? FormatoImpresion::TICKET_80MM;

        return $this->impresionService->renderAbono($abono, $formato);
    }
}
