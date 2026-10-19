<?php

namespace Tests\Feature\DesignSystem;

use App\Enums\EstadoCaja;
use App\Enums\EstadoCuentaCobrar;
use App\Enums\EstadoGeneral;
use App\Enums\EstadoSesionCaja;
use App\Enums\EstadoSuscripcion;
use App\Enums\EstadoVenta;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class ComponentTest extends TestCase
{
    public function test_badge_renders_correctly(): void
    {
        $rendered = Blade::render('<x-badge variant="success" dot>Completado</x-badge>');
        $this->assertStringContainsString('badge-success', $rendered);
        $this->assertStringContainsString('bg-emerald-500', $rendered);
        $this->assertStringContainsString('Completado', $rendered);

        $renderedDanger = Blade::render('<x-badge variant="danger">Error</x-badge>');
        $this->assertStringContainsString('badge-danger', $renderedDanger);
        $this->assertStringNotContainsString('bg-emerald-500', $renderedDanger);
    }

    public function test_status_badge_maps_enums_and_strings_semantically(): void
    {
        // EstadoVenta::COMPLETADA -> success
        $renderedVenta = Blade::render('<x-status-badge :status="$status" />', [
            'status' => EstadoVenta::COMPLETADA,
        ]);
        $this->assertStringContainsString('badge-success', $renderedVenta);
        $this->assertStringContainsString('Completada', $renderedVenta);

        // EstadoVenta::ANULADA -> danger
        $renderedAnulada = Blade::render('<x-status-badge :status="$status" />', [
            'status' => EstadoVenta::ANULADA,
        ]);
        $this->assertStringContainsString('badge-danger', $renderedAnulada);
        $this->assertStringContainsString('Anulada', $renderedAnulada);

        // EstadoSesionCaja::ABIERTA -> success
        $renderedCaja = Blade::render('<x-status-badge :status="$status" />', [
            'status' => EstadoSesionCaja::ABIERTA,
        ]);
        $this->assertStringContainsString('badge-success', $renderedCaja);
        $this->assertStringContainsString('Abierta', $renderedCaja);

        // EstadoCuentaCobrar::PENDIENTE -> warning
        $renderedCartera = Blade::render('<x-status-badge :status="$status" />', [
            'status' => EstadoCuentaCobrar::PENDIENTE,
        ]);
        $this->assertStringContainsString('badge-warning', $renderedCartera);
        $this->assertStringContainsString('Pendiente', $renderedCartera);

        // EstadoSuscripcion::PRUEBA -> info
        $renderedSub = Blade::render('<x-status-badge :status="$status" />', [
            'status' => EstadoSuscripcion::PRUEBA,
        ]);
        $this->assertStringContainsString('badge-info', $renderedSub);

        // String directo
        $renderedString = Blade::render('<x-status-badge status="ACTIVO" />');
        $this->assertStringContainsString('badge-success', $renderedString);
        $this->assertStringContainsString('Activo', $renderedString);
    }

    public function test_button_renders_variants_and_sizes(): void
    {
        $renderedPrimary = Blade::render('<x-button variant="primary" size="md">Guardar</x-button>');
        $this->assertStringContainsString('bg-brand-600', $renderedPrimary);
        $this->assertStringContainsString('h-9', $renderedPrimary);
        $this->assertStringContainsString('Guardar', $renderedPrimary);

        $renderedDanger = Blade::render('<x-button variant="danger" size="sm">Eliminar</x-button>');
        $this->assertStringContainsString('bg-rose-600', $renderedDanger);
        $this->assertStringContainsString('h-8', $renderedDanger);

        $renderedLink = Blade::render('<x-button href="https://example.com">Enlace</x-button>');
        $this->assertStringContainsString('<a', $renderedLink);
        $this->assertStringContainsString('href="https://example.com"', $renderedLink);
    }

    public function test_icon_button_renders_accessible_attributes(): void
    {
        $rendered = Blade::render('<x-icon-button title="Editar elemento" aria-label="Editar"><span>Icon</span></x-icon-button>');
        $this->assertStringContainsString('title="Editar elemento"', $rendered);
        $this->assertStringContainsString('aria-label="Editar"', $rendered);
        $this->assertStringContainsString('w-8 h-8', $rendered);
    }

    public function test_kpi_card_renders_metrics_and_trends(): void
    {
        $rendered = Blade::render('<x-kpi-card label="Ventas Totales" value="$500,000" context="Hoy" trend="up" trendValue="+12%" />');
        $this->assertStringContainsString('Ventas Totales', $rendered);
        $this->assertStringContainsString('$500,000', $rendered);
        $this->assertStringContainsString('tabular-nums', $rendered);
        $this->assertStringContainsString('+12%', $rendered);
        $this->assertStringContainsString('text-emerald-700', $rendered);

        $renderedAlert = Blade::render('<x-kpi-card label="Stock Bajo" value="3" alert="danger" />');
        $this->assertStringContainsString('border-rose-300', $renderedAlert);
    }

    public function test_field_renders_label_required_and_errors(): void
    {
        $rendered = Blade::render('<x-field label="Nombre Fiscal" name="nombre" required hint="Ayuda"><input name="nombre" /></x-field>');
        $this->assertStringContainsString('Nombre Fiscal', $rendered);
        $this->assertStringContainsString('*', $rendered);
        $this->assertStringContainsString('Ayuda', $rendered);

        $renderedError = Blade::render('<x-field label="Email" error="Correo inválido"><input name="email" /></x-field>');
        $this->assertStringContainsString('Correo inválido', $renderedError);
        $this->assertStringContainsString('text-rose-600', $renderedError);
    }

    public function test_empty_state_renders_title_and_description(): void
    {
        $rendered = Blade::render('<x-empty-state title="Sin resultados" description="Intente otra búsqueda" />');
        $this->assertStringContainsString('Sin resultados', $rendered);
        $this->assertStringContainsString('Intente otra búsqueda', $rendered);
    }

    public function test_design_system_preview_view_compiles_successfully(): void
    {
        $view = view('design-system-preview')->render();
        $this->assertNotEmpty($view);
        $this->assertStringContainsString('Sistema de Diseño — Banca Corporativa Moderna', $view);
        $this->assertStringContainsString('Tarjetas de Métricas', $view);
    }
}
