<?php

namespace App\Http\Controllers;

use App\Mail\DocumentoComercialMail;
use App\Models\Cotizacion;
use App\Models\EnlaceCompartido;
use App\Models\Venta;
use App\Services\DocumentoPdfService;
use App\Support\Tenancy\CompanyContext;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Envío de facturas de venta y cotizaciones: PDF descargable, correo con adjunto
 * y enlace corto y temporal para compartir por WhatsApp Web (sin API).
 */
class EnvioDocumentoController extends Controller
{
    public function __construct(
        protected DocumentoPdfService $pdfService
    ) {}

    public function pdfVenta(Request $request, Venta $venta): Response
    {
        $this->autorizarVenta($venta);

        $pdf = $this->pdfService->venta($venta);
        $archivo = $this->pdfService->nombreArchivo($venta);

        return $request->boolean('ver') ? $pdf->stream($archivo) : $pdf->download($archivo);
    }

    public function correoVenta(Request $request, Venta $venta): RedirectResponse
    {
        $this->autorizarVenta($venta);

        return $this->enviarCorreo($request, $venta);
    }

    public function correoCotizacion(Request $request, Cotizacion $cotizacion): RedirectResponse
    {
        if (Gate::denies('view', $cotizacion)) {
            abort(403, 'No tienes autorización para enviar esta cotización.');
        }

        return $this->enviarCorreo($request, $cotizacion);
    }

    /**
     * PDF público para el cliente desde el enlace corto. Sin sesión ni contexto de empresa: solo con un token vigente.
     * El nombre del archivo en la URL es decorativo (para que el cliente vea qué documento abre).
     */
    public function pdfCompartido(string $token): Response
    {
        $enlace = EnlaceCompartido::where('token', $token)->first();

        if (! $enlace || ! $enlace->estaVigente()) {
            abort(410, 'Este enlace ya no está disponible. Solicita el documento nuevamente.');
        }

        $documento = $enlace->documento() ?? abort(404);
        $enlace->increment('visitas');

        $pdf = $documento instanceof Venta ? $this->pdfService->venta($documento) : $this->pdfService->cotizacion($documento);

        return $pdf->stream($this->pdfService->nombreArchivo($documento));
    }

    /**
     * Enlace corto al PDF para el mensaje de WhatsApp, ej: https://dominio/d/k7Qm2xPa9TbR/COT-00001.pdf
     */
    public static function enlaceCompartido(Venta|Cotizacion $documento): string
    {
        return route('documentos.compartido', [
            'token' => EnlaceCompartido::paraDocumento($documento)->token,
            'archivo' => app(DocumentoPdfService::class)->nombreArchivo($documento),
        ]);
    }

    /**
     * Textos sugeridos para el envío. El de WhatsApp usa su formato (*negrita* y saltos de línea)
     * y deja el enlace solo en su línea para que se muestre clicable.
     *
     * @return array{etiqueta: string, correo: string, whatsapp: string}
     */
    public static function mensajes(Venta|Cotizacion $documento): array
    {
        $esVenta = $documento instanceof Venta;
        $cliente = $documento->cliente;
        $empresa = $documento->empresa?->nombre_comercial ?? config('app.name');
        $etiqueta = $esVenta
            ? $documento->tipo_comprobante->label().' '.$documento->numero_venta
            : 'cotización '.$documento->numero_cotizacion;
        $nombreCliente = $cliente && ! $cliente->es_predeterminado ? $cliente->razon_social : null;
        $total = '$'.number_format((float) $documento->total, 0, ',', '.');

        $saludo = $nombreCliente ? "Hola {$nombreCliente}," : 'Hola,';
        $correo = "{$saludo}\n\nTe compartimos la {$etiqueta} de {$empresa} por un total de {$total}. Encuentras el documento adjunto en PDF.\n\nQuedamos atentos a cualquier inquietud.";

        $lineas = [
            $nombreCliente ? "Hola *{$nombreCliente}*," : 'Hola,',
            '',
            'Te compartimos la *'.ucfirst($etiqueta)."* de *{$empresa}*.",
            '',
            "Total: *{$total}*",
        ];
        if (! $esVenta && $documento->fecha_validez) {
            $lineas[] = 'Válida hasta: '.$documento->fecha_validez->format('d/m/Y');
        }
        array_push($lineas, '', 'Ver o descargar el PDF:', self::enlaceCompartido($documento), '', 'Quedamos atentos a cualquier inquietud.');

        return [
            'etiqueta' => $etiqueta,
            'correo' => $correo,
            'whatsapp' => implode("\n", $lineas),
        ];
    }

    protected function enviarCorreo(Request $request, Venta|Cotizacion $documento): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email:rfc', 'max:150'],
            'mensaje' => ['required', 'string', 'max:2000'],
        ]);

        $pdf = $documento instanceof Venta ? $this->pdfService->venta($documento) : $this->pdfService->cotizacion($documento);
        $empresa = $documento->empresa?->nombre_comercial ?? config('app.name');
        $etiqueta = $documento instanceof Venta
            ? $documento->tipo_comprobante->label().' '.$documento->numero_venta
            : 'Cotización '.$documento->numero_cotizacion;

        try {
            Mail::to($validated['email'])->send(new DocumentoComercialMail(
                asunto: "{$etiqueta} - {$empresa}",
                mensaje: $validated['mensaje'],
                empresaNombre: $empresa,
                pdfContenido: $pdf->output(),
                pdfNombre: $this->pdfService->nombreArchivo($documento)
            ));
        } catch (Exception $e) {
            Log::error('Fallo al enviar documento por correo', ['documento' => $etiqueta, 'error' => $e->getMessage()]);

            return back()->with('error', 'No se pudo enviar el correo. Verifica la configuración de correo del sistema.');
        }

        return back()->with('success', "{$etiqueta} enviada a {$validated['email']}.");
    }

    protected function autorizarVenta(Venta $venta): void
    {
        if (CompanyContext::getId() && CompanyContext::getId() !== $venta->empresa_id) {
            abort(404);
        }

        Gate::authorize('view', $venta);
    }
}
