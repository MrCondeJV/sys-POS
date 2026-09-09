<?php

namespace App\Http\Controllers;

use App\Models\NotificacionSistema;
use App\Support\Tenancy\CompanyContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificacionController extends Controller
{
    public function index(Request $request): View
    {
        $query = NotificacionSistema::latest();

        if ($request->query('filtro') === 'no_leidas') {
            $query->noLeidas();
        } elseif ($request->query('filtro') === 'leidas') {
            $query->leidas();
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        $notificaciones = $query->paginate(20)->withQueryString();
        $totalNoLeidas = NotificacionSistema::noLeidas()->count();

        return view('notificaciones.index', compact('notificaciones', 'totalNoLeidas'));
    }

    public function marcarLeida(Request $request, NotificacionSistema $notificacion): RedirectResponse|JsonResponse
    {
        if (CompanyContext::getId() && CompanyContext::getId() !== $notificacion->empresa_id) {
            abort(404);
        }

        $notificacion->marcarComoLeida();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        if ($notificacion->url_accion) {
            return redirect($notificacion->url_accion);
        }

        return back()->with('success', 'Notificación marcada como leída.');
    }

    public function marcarTodasLeidas(Request $request): RedirectResponse|JsonResponse
    {
        NotificacionSistema::noLeidas()->update([
            'leida' => true,
            'leida_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Todas las notificaciones fueron marcadas como leídas.');
    }

    public function conteoNoLeidas(): JsonResponse
    {
        $conteo = NotificacionSistema::noLeidas()->count();
        $recientes = NotificacionSistema::noLeidas()->latest()->take(5)->get();

        return response()->json([
            'conteo' => $conteo,
            'recientes' => $recientes,
        ]);
    }
}
