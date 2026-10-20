<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-app overflow-hidden">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'POS Comercial') }} - @yield('title', 'Inicio')</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <!-- PWA Manifest & Service Worker -->
    <link rel="manifest" href="/manifest.json">
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(() => {});
            });
        }
    </script>
    <!-- Vite: CSS y JS compilados con Tailwind CSS v4 (Fuente única de verdad en resources/css/app.css) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @php
        $empresaActualLayout = \App\Support\Tenancy\CompanyContext::getCompany() ?? (auth()->check() ? auth()->user()->empresa : null);
    @endphp

    @yield('styles')
    @livewireStyles
</head>
<body class="h-full font-sans antialiased text-text-primary bg-app flex overflow-hidden"
      x-data="{
          mobileMenuOpen: false,
          modalOpen: false,
          posMode: {{ request()->routeIs('pos.*') ? 'true' : 'false' }},
          sidebarCollapsed: {{ request()->routeIs('pos.*') ? 'true' : 'false' }},
          hoverOpen: false,
          pinned: false,
          _openT: null,
          _closeT: null,
          get expanded() {
              if (this.posMode) {
                  return this.pinned || this.hoverOpen;
              }
              return !this.sidebarCollapsed;
          },
          hasOpenModal() {
              if (this.modalOpen || window.__posModalOpen) return true;
              return Array.from(document.querySelectorAll('[role=\'dialog\'][aria-modal=\'true\']'))
                  .some((el) => !el.hasAttribute('data-mobile-drawer') && window.getComputedStyle(el).display !== 'none');
          },
          enter() {
              if (!this.posMode || window.innerWidth < 1024 || this.pinned) return;
              if (this.hasOpenModal()) return;
              clearTimeout(this._closeT);
              this._openT = setTimeout(() => { this.hoverOpen = true; }, 120);
          },
          leave() {
              if (!this.posMode || this.pinned) return;
              clearTimeout(this._openT);
              this._closeT = setTimeout(() => { this.hoverOpen = false; }, 250);
          },
          leaveNow() {
              clearTimeout(this._openT);
              clearTimeout(this._closeT);
              this.hoverOpen = false;
          },
          toggleSidebar() {
              if (window.innerWidth < 1024) {
                  this.mobileMenuOpen = !this.mobileMenuOpen;
              } else if (this.posMode) {
                  this.pinned = !this.pinned;
                  this.sidebarCollapsed = !this.pinned;
              } else {
                  this.sidebarCollapsed = !this.sidebarCollapsed;
              }
          }
      }">

    @auth
        @include('layouts.partials.sidebar')
    @endauth

    <!-- Área Principal de Contenido -->
    <div class="flex-1 flex flex-col min-w-0 min-h-0 h-full overflow-hidden">
        @auth
            @include('layouts.partials.context-banner')
            @include('layouts.partials.header')
        @endauth

        <main class="flex-1 min-h-0 overflow-y-auto overflow-x-hidden p-4 sm:p-6 lg:p-8">
            @include('layouts.partials.flash-messages')

            @yield('content')
        </main>
    </div>

    @livewireScripts

    <script>
        /* Auto-scroll del Sidebar al ítem activo */
        (function () {
            const nav = document.querySelector('aside nav');
            if (!nav) return;

            const activeLink = nav.querySelector('a.border-brand-500, a.bg-brand-600');
            if (activeLink) {
                activeLink.scrollIntoView({ block: 'center', behavior: 'instant' });
            }
        })();

        /* Búsqueda Dinámica en Tiempo Real (Debounce 300ms) */
        (function () {
            let searchDebounceTimer = null;
            let searchAbortController = null;
            const searchInputSelector = 'input[name="buscar"], input[name="search"], input[name="q"]';

            function esFormularioBusqueda(input) {
                const form = input.closest('form');
                return form && form.method.toUpperCase() === 'GET';
            }

            async function ejecutarBusquedaDinamica(form, inputActual) {
                const mainContainer = document.querySelector('main');
                if (!mainContainer) return;

                if (searchAbortController) {
                    searchAbortController.abort();
                }
                searchAbortController = new AbortController();

                const formData = new FormData(form);
                const params = new URLSearchParams();
                for (const [key, value] of formData.entries()) {
                    if (value !== '' && value !== null) {
                        params.append(key, value);
                    }
                }

                const actionUrl = form.getAttribute('action') || window.location.pathname;
                const targetUrl = actionUrl + (params.toString() ? '?' + params.toString() : '');

                const iconContainer = inputActual.closest('.relative')?.querySelector('.pointer-events-none');
                if (iconContainer) {
                    iconContainer.classList.add('animate-pulse', 'text-brand-600');
                }

                try {
                    const response = await fetch(targetUrl, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        signal: searchAbortController.signal
                    });

                    if (!response.ok) throw new Error('Respuesta no satisfactoria');

                    const html = await response.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newMain = doc.querySelector('main');

                    if (newMain) {
                        const inputName = inputActual.getAttribute('name');
                        const cursorPos = inputActual.selectionStart;
                        const scrollY = mainContainer.scrollTop;

                        mainContainer.innerHTML = newMain.innerHTML;
                        window.history.replaceState({}, '', targetUrl);

                        const newInput = mainContainer.querySelector(`input[name="${inputName}"]`);
                        if (newInput) {
                            newInput.focus();
                            if (cursorPos !== null) {
                                try {
                                    newInput.setSelectionRange(cursorPos, cursorPos);
                                } catch (err) {}
                            }
                        }

                        mainContainer.scrollTop = scrollY;
                    }
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        console.warn('Búsqueda dinámica fallback:', error);
                    }
                } finally {
                    if (iconContainer) {
                        iconContainer.classList.remove('animate-pulse', 'text-brand-600');
                    }
                }
            }

            document.addEventListener('input', function (e) {
                const target = e.target;
                if (!target.matches || !target.matches(searchInputSelector)) return;
                const form = target.closest('form');
                if (!form || !esFormularioBusqueda(target)) return;

                clearTimeout(searchDebounceTimer);
                searchDebounceTimer = setTimeout(() => {
                    ejecutarBusquedaDinamica(form, target);
                }, 300);
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    const target = e.target;
                    if (!target.matches || !target.matches(searchInputSelector)) return;
                    const form = target.closest('form');
                    if (!form || !esFormularioBusqueda(target)) return;

                    e.preventDefault();
                    clearTimeout(searchDebounceTimer);
                    ejecutarBusquedaDinamica(form, target);
                }
            });

            document.addEventListener('change', function (e) {
                const target = e.target;
                if (!target.matches || !target.matches('form select, form input[type="checkbox"]')) return;
                const form = target.closest('form');
                if (!form || form.method.toUpperCase() !== 'GET') return;

                if (form.querySelector(searchInputSelector)) {
                    const searchInput = form.querySelector(searchInputSelector);
                    clearTimeout(searchDebounceTimer);
                    ejecutarBusquedaDinamica(form, searchInput || target);
                }
            });
        })();
    </script>
</body>
</html>
