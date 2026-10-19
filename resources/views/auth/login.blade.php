@extends('layouts.app')

@section('title', 'Iniciar Sesión — ' . config('app.name', 'sys-POS'))

@section('content')
<div class="min-h-[80vh] flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <div class="inline-flex items-center justify-center h-12 w-12 rounded-xl bg-slate-900 text-white shadow-sm mb-4">
            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
            Sistema POS Comercial
        </h1>
        <p class="mt-1 text-sm text-slate-500">
            Ingresa tus credenciales corporativas para acceder al punto de venta
        </p>
    </div>

    <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="card p-6 sm:p-8">
            @if ($errors->any())
                <div class="mb-5 rounded-lg bg-rose-50 p-3.5 border border-rose-200 text-xs text-rose-800">
                    <div class="font-semibold mb-1">Error de autenticación:</div>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="space-y-4" action="{{ route('login') }}" method="POST">
                @csrf

                <div>
                    <label for="email" class="label-base">
                        Correo Electrónico
                    </label>
                    <input id="email" name="email" type="email" autocomplete="email" required
                        value="{{ old('email') }}"
                        class="input-base"
                        placeholder="usuario@comercio.com">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="label-base mb-0">
                            Contraseña
                        </label>
                    </div>
                    <input id="password" name="password" type="password" autocomplete="current-password" required
                        class="input-base"
                        placeholder="••••••••">
                </div>

                <div class="flex items-center justify-between pt-1">
                    <div class="flex items-center gap-2">
                        <input id="remember" name="remember" type="checkbox"
                            class="h-4 w-4 text-brand-600 focus:ring-brand-500 border-slate-300 rounded">
                        <label for="remember" class="text-xs text-slate-600 select-none cursor-pointer">
                            Recordar sesión
                        </label>
                    </div>

                    <div class="text-xs">
                        <span class="text-slate-400 hover:text-slate-600 cursor-pointer">
                            ¿Olvidaste tu contraseña?
                        </span>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn-primary w-full justify-center py-2.5">
                        Iniciar Sesión
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-6 text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} {{ config('app.name', 'sys-POS') }}. Todos los derechos reservados.
        </div>
    </div>
</div>
@endsection
