<?php

namespace App\Http\Controllers;

use App\Enums\EstadoDian;
use App\Models\DocumentoElectronico;
use App\Models\DocumentoVenta;
use App\Services\FacturacionElectronicaService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FacturacionElectronicaController extends Controller
{
    public function index(Request $request): View
    {
        $query = DocumentoElectronico::with(['documentoVenta.venta', 'documentoVenta.cliente', 'resolucion'])
            ->latest('id');

        if ($request->filled('estado')) {
            $query->where('estado_dian', $request->estado);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($b) use ($q) {
                $b->where('consecutivo_completo', 'like', "%{$q}%")
                  ->orWhere('cufe', 'like', "%{$q}%");
            });
        }

        $documentos = $query->paginate(20);
        $estadosDian = EstadoDian::cases();

        return view('facturacion-electronica.documentos.index', compact('documentos', 'estadosDian'));
    }

    public function show(DocumentoElectronico $documento): View
    {
        $documento->load(['documentoVenta.venta.detalles.producto', 'documentoVenta.cliente', 'resolucion']);

        $xmlContent = null;
        if ($documento->xml_path && Storage::disk('local')->exists($documento->xml_path)) {
            $xmlContent = Storage::disk('local')->get($documento->xml_path);
        }

        return view('facturacion-electronica.documentos.show', compact('documento', 'xmlContent'));
    }

    public function emitir(Request $request, DocumentoVenta $documentoVenta, FacturacionElectronicaService $service): RedirectResponse
    {
        try {
            $docElectronico = $service->emitirFacturaElectronica($documentoVenta);

            return redirect()->route('facturacion-electronica.show', $docElectronico)
                ->with('success', "Factura electrónica {$docElectronico->consecutivo_completo} emitida y validada con éxito ante la DIAN.");
        } catch (Exception $e) {
            return back()->with('error', 'Error emitiendo factura electrónica: ' . $e->getMessage());
        }
    }

    public function notaCredito(Request $request, DocumentoElectronico $documento, FacturacionElectronicaService $service): RedirectResponse
    {
        $request->validate([
            'motivo' => ['required', 'string', 'max:255'],
        ]);

        try {
            $nc = $service->emitirNotaCredito($documento, $request->motivo);

            return redirect()->route('facturacion-electronica.show', $nc)
                ->with('success', "Nota Crédito Electrónica {$nc->consecutivo_completo} emitida y aceptada con éxito ante la DIAN.");
        } catch (Exception $e) {
            return back()->with('error', 'Error emitiendo nota crédito: ' . $e->getMessage());
        }
    }

    public function descargarXml(DocumentoElectronico $documento): StreamedResponse|RedirectResponse
    {
        if (!$documento->xml_path || !Storage::disk('local')->exists($documento->xml_path)) {
            return back()->with('error', 'El archivo XML no se encuentra disponible.');
        }

        return Storage::disk('local')->download($documento->xml_path, "{$documento->consecutivo_completo}.xml", [
            'Content-Type' => 'application/xml',
        ]);
    }
}
