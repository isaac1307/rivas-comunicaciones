<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use App\Models\Cliente;
use App\Models\MensajeContacto;
use App\Models\Plan;
use App\Models\Reservacion;
use App\Models\Ticket;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    /**
     * Muestra las estadísticas y métricas del panel administrativo.
     */
    public function index(): View
    {
        $metricas = [
            'totalClientes' => Cliente::count(),
            'clientesActivos' => Cliente::where('estado', 'activo')->count(),
            'ticketsAbiertos' => Ticket::whereIn('estado', ['abierto', 'en_proceso'])->count(),
            'ticketsResueltos' => Ticket::where('estado', 'resuelto')->count(),
            'reservacionesPendientes' => Reservacion::where('estado', 'pendiente')->count(),
            'reservacionesProgramadas' => Reservacion::where('estado', 'programada')->count(),
            'totalPlanes' => Plan::where('estado', 'activo')->count(),
            'mensajesNoLeidos' => MensajeContacto::where('leido', false)->count(),
        ];

        $ultimosTickets = Ticket::with('cliente')
            ->latest()
            ->take(5)
            ->get();

        $ultimasReservaciones = Reservacion::with('plan')
            ->latest()
            ->take(5)
            ->get();

        $ultimasBitacoras = Bitacora::with('usuario')
            ->latest()
            ->take(6)
            ->get();

        return view('admin.dashboard', compact('metricas', 'ultimosTickets', 'ultimasReservaciones', 'ultimasBitacoras'));
    }
}
