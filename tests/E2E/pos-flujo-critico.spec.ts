/**
 * Esqueleto E2E Playwright — Fase A7 (sys-POS)
 * Ejecutar con: npx playwright test tests/E2E/pos-flujo-critico.spec.ts
 */

export const scenarios = [
    {
        id: 'E2E-01',
        name: 'Flujo completo Cajero: Login -> Abrir Caja -> Venta POS Efectivo (F2/F8) -> Cierre Reporte Z',
        viewport: { width: 1280, height: 800 },
        steps: [
            'GET /login -> completar email/password -> submit',
            'Verificar redirección a /dashboard y presencia de sidebar brand-900',
            'GET /cajas -> abrir modal de apertura -> ingresar monto_apertura = 100000 -> submit',
            'GET /pos -> verificar riel lateral de 80px y ausencia de robo de foco del buscador',
            'Presionar F2 -> buscar artículo por código -> agregar al carrito',
            'Presionar F8 -> verificar role="dialog" y aria-modal="true" en modal de cobro',
            'Confirmar cobro en efectivo -> verificar descuento de stock y generación de ticket',
            'GET /cajas/{id}/cierre -> ingresar monto_cierre_contado -> confirmar cierre',
            'Verificar /cajas/comprobantes/{sesion} en modo Carta y Térmica 80mm sin recursos externos',
        ],
    },
    {
        id: 'E2E-02',
        name: 'Aislamiento visual y funcional en Tablet (1024x768) en Terminal POS',
        viewport: { width: 1024, height: 768 },
        steps: [
            'GET /pos en 1024x768 -> expandir sidebar overlay -> clic fuera (@click.outside) -> verificar cierre del overlay',
            'Alternar vista cuadrícula / lista en catálogo de productos del POS',
        ],
    },
];
