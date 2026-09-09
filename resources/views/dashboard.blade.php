@extends('layouts.app')

@section('title', 'Panel de Control')

@section('content')
<div class="min-h-full">
    <!-- Navbar superior -->
    <nav class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-3">
                    <div class="h-9 w-9 bg-indigo-600 rounded-lg flex items-center justify-center text-white font-bold">
                        POS
                    </div>
                    <span class="font-bold text-lg text-slate-900">
                        {{ auth()->user()->empresa?->nombre_comercial ?? 'POS Comercial Global' }}
                    </span>
                    @if(auth()->user()->sucursal)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                            {{ auth()->user()->sucursal->nombre }}
                        </span>
                    @endif
                </div>

                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <div class="text-sm font-semibold text-slate-800">{{ auth()->user()->name }}</div>
                        <div class="text-xs text-slate-500">
                            {{ auth()->user()->roles->first()?->name ?? auth()->user()->cargo ?? 'Usuario' }}
                        </div>
                    </div>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="p-2 text-slate-500 hover:text-red-600 transition" title="Cerrar Sesión">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <main class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-slate-200 p-8">
                <div class="border-b border-slate-200 pb-5 mb-6">
                    <h1 class="text-2xl font-bold text-slate-900">Bienvenido al Sistema POS</h1>
                    <p class="text-sm text-slate-500 mt-1">Sesión autenticada correctamente bajo el tenant activo.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-slate-50 p-5 rounded-xl border border-slate-200">
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Empresa</span>
                        <div class="text-lg font-bold text-slate-800 mt-1">
                            {{ auth()->user()->empresa?->nombre_comercial ?? 'Super Admin (Transversal)' }}
                        </div>
                        <div class="text-xs text-slate-500 mt-0.5">
                            NIT: {{ auth()->user()->empresa?->nit ?? 'N/A' }}
                        </div>
                    </div>

                    <div class="bg-slate-50 p-5 rounded-xl border border-slate-200">
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Sucursal Activa</span>
                        <div class="text-lg font-bold text-slate-800 mt-1">
                            {{ auth()->user()->sucursal?->nombre ?? 'Todas / Sin asignar' }}
                        </div>
                        <div class="text-xs text-slate-500 mt-0.5">
                            {{ auth()->user()->sucursal?->ciudad ?? 'N/A' }}
                        </div>
                    </div>

                    <div class="bg-slate-50 p-5 rounded-xl border border-slate-200">
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Rol de Usuario</span>
                        <div class="text-lg font-bold text-indigo-600 mt-1">
                            {{ auth()->user()->roles->first()?->name ?? 'Sin Rol Asignado' }}
                        </div>
                        <div class="text-xs text-emerald-600 font-medium mt-0.5 flex items-center">
                            <span class="h-2 w-2 rounded-full bg-emerald-500 mr-1.5"></span>
                            Cuenta Activa
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection
