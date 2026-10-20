# Suite E2E (Playwright) — `sys-POS` (Fase A7)

Esta carpeta contiene los escenarios End-to-End (E2E) de navegador real para validar los flujos críticos de caja, terminal POS, aislamiento de roles y vistas de impresión sin modificar de forma invasiva el `package.json` de producción.

## Cómo ejecutar localmente cuando se desee instalar el runner de Playwright

```powershell
npx playwright test tests/E2E/pos-flujo-critico.spec.ts
```

## Escenarios Cubiertos en `pos-flujo-critico.spec.ts`
1. **Autenticación y Contexto de Rol:** Inicio de sesión como `ADMIN_EMPRESA` y `CAJERO`, verificando que el sidebar colapsado y el encabezado institucional muestran la empresa y sede activa.
2. **Apertura de Turno de Caja:** Navegación a `/cajas`, apertura de caja con monto inicial y redirección a la terminal `/pos`.
3. **Operación de Terminal POS (Teclado y Mouse):**
   - Sidebar colapsado en riel de 80px que se despliega como overlay (`z-40`) al pasar el mouse con retraso de 120ms y se cierra con `Esc`.
   - Búsqueda de producto con `F2`, ajuste de cantidades en el carrito, selección de cliente (`F4`) y apertura del modal de cobro (`F8`).
   - Cobro en efectivo con cálculo de cambio en vivo y emisión de venta.
4. **Cierre de Turno y Reporte Z (Carta y Térmica 80mm):**
   - Arqueo de cierre en `/cajas/{id}/cierre`, verificación de diferencia $0.00 y vista previa de impresión `/cajas/comprobantes/{sesion}` alternando entre formato Carta y Térmica 80mm.
