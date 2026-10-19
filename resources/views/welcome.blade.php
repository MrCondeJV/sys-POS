<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'sys-POS') }} — Sistema POS & Gestión Comercial</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="h-full flex items-center justify-center p-6 text-slate-800">
        <div class="card max-w-md w-full p-8 text-center space-y-4">
            <div class="inline-flex items-center justify-center h-12 w-12 rounded-xl bg-slate-900 text-white shadow-sm mx-auto">
                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
            </div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">{{ config('app.name', 'sys-POS') }}</h1>
            <p class="text-sm text-slate-500">Sistema POS multiempresa y gestión comercial institucional.</p>
            <div class="pt-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-primary w-full justify-center">Ir al Dashboard &rarr;</a>
                @else
                    <a href="{{ route('login') }}" class="btn-primary w-full justify-center">Acceder al Sistema &rarr;</a>
                @endauth
            </div>
        </div>
    </body>
</html>
