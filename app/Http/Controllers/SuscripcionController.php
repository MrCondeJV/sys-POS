<?php

namespace App\Http\Controllers;

use App\Models\PagoSuscripcion;
use App\Models\Plan;
use App\Services\Wompi\WompiService;
use App\Support\Tenancy\CompanyContext;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SuscripcionController extends Controller
{
    public function __construct(
        protected WompiService $wompiService
    ) {}

    /**
     * Pantalla de suscripción del tenant: estado actual, planes disponibles y pagos previos.
     */
    public function index(): View
    {
        $empresa = CompanyContext::getCompany();
        if (! $empresa) {
            abort(404, 'Empresa no encontrada.');
        }

        $suscripcion = $empresa->suscripcionActual;
        $planActual = $empresa->obtenerPlan();

        $planes = Plan::where('activo', true)
            ->orderBy('orden_visual')
            ->orderBy('precio_mensual')
            ->get();

        $pagosRecientes = $empresa->pagosSuscripcion()
            ->latest()
            ->take(8)
            ->get();

        return view('suscripcion.index', compact(
            'empresa',
            'suscripcion',
            'planActual',
            'planes',
            'pagosRecientes'
        ));
    }

    /**
     * Genera la intención de pago criptográfica y renderiza el Widget de Wompi.
     */
    public function checkout(Plan $plan, Request $request): View
    {
        $empresa = CompanyContext::getCompany();
        if (! $empresa) {
            abort(404, 'Empresa no encontrada.');
        }

        $ciclo = strtoupper($request->input('ciclo', 'MENSUAL'));
        if (! in_array($ciclo, ['MENSUAL', 'ANUAL'], true)) {
            $ciclo = 'MENSUAL';
        }

        $pago = $this->wompiService->crearIntencionPago(
            $empresa,
            $plan,
            $ciclo,
            $request->user()
        );

        $wompiConfig = [
            'publicKey' => config('services.wompi.public_key'),
            'currency' => $pago->currency,
            'amountInCents' => $pago->amount_in_cents,
            'reference' => $pago->reference,
            'signature' => $pago->integrity_signature,
            'redirectUrl' => route('suscripcion.retorno'),
        ];

        return view('suscripcion.checkout', compact('empresa', 'plan', 'pago', 'wompiConfig'));
    }

    /**
     * Retorno del usuario tras completar el checkout en Wompi.
     */
    public function retorno(Request $request): View
    {
        $empresa = CompanyContext::getCompany();
        $wompiTxId = $request->query('id');

        $pago = null;
        if ($wompiTxId) {
            $pago = PagoSuscripcion::where('wompi_transaction_id', $wompiTxId)->first();
        }

        return view('suscripcion.retorno', compact('empresa', 'wompiTxId', 'pago'));
    }
}
