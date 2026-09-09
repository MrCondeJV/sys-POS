# ROADMAP — Sistema POS Comercial

## 1. Objetivo del proyecto

Construir un sistema POS comercial robusto, seguro, escalable y multiempresa para pequeños y medianos negocios de Colombia.

El sistema estará orientado inicialmente a:

- Tiendas
- Minimercados
- Ferreterías
- Almacenes
- Droguerías
- Papelerías
- Comercios minoristas

El sistema deberá evolucionar posteriormente hacia:

- POS
- Inventario
- Compras
- Ventas
- Caja
- Clientes
- Crédito y cartera
- Proveedores
- Reportes
- Multiempresa
- Multisucursal
- Facturación electrónica
- API
- SaaS

---

# 2. Stack tecnológico

## Backend

- Laravel 12
- PHP 8.3+
- MySQL 8+
- Redis
- Laravel Queue
- Laravel Scheduler

## Frontend

- Blade
- Livewire 3
- Alpine.js
- Tailwind CSS

## API

- Laravel Sanctum
- REST API
- `/api/v1`

## Seguridad

- Spatie Laravel Permission
- Policies
- Gates
- Rate Limiting
- Auditoría

## Documentos

- DomPDF
- Laravel Excel

## Infraestructura futura

- Ubuntu
- Nginx
- PHP-FPM
- MySQL
- Redis
- Docker
- Cloudflare

---

# 3. Arquitectura

Utilizar:

**Monolito Modular**

NO utilizar microservicios inicialmente.

La aplicación debe separar correctamente:

```text
Presentación
     ↓
Aplicación
     ↓
Dominio
     ↓
Infraestructura
     ↓
Persistencia
```

Evitar colocar lógica de negocio compleja directamente en:

- Controllers
- Componentes Livewire
- Views
- Routes

Utilizar cuando corresponda:

- Services
- Actions
- DTOs
- Policies
- Events
- Listeners
- Jobs
- Enums
- Value Objects
- Form Requests

No crear patrones innecesarios.

---

# 4. Estructura general

La arquitectura deberá evolucionar hacia una estructura similar:

```text
app/
├── Modules/
│   ├── Auth/
│   ├── Empresas/
│   ├── Usuarios/
│   ├── Sucursales/
│   ├── Productos/
│   ├── Inventario/
│   ├── Ventas/
│   ├── Compras/
│   ├── Clientes/
│   ├── Proveedores/
│   ├── Caja/
│   ├── Reportes/
│   ├── Impuestos/
│   ├── Configuracion/
│   ├── Auditoria/
│   └── FacturacionElectronica/
│
├── Actions/
├── Services/
├── DTOs/
├── Jobs/
├── Events/
├── Listeners/
├── Policies/
├── Enums/
└── Support/
```

---

# 5. Regla principal de desarrollo

El proyecto debe desarrollarse por fases.

NO implementar todas las funcionalidades simultáneamente.

Cada fase debe:

1. Analizar los módulos existentes.
2. Revisar `ARCHITECTURE.md`.
3. Revisar las migraciones existentes.
4. Diseñar las entidades necesarias.
5. Implementar.
6. Crear pruebas.
7. Ejecutar pruebas.
8. Corregir errores.
9. Revisar seguridad.
10. Revisar aislamiento multiempresa.
11. Verificar que no se rompan módulos anteriores.

No avanzar a la siguiente fase si existen errores críticos.

---

# FASE 0 — Análisis y arquitectura

## Objetivo

Analizar el proyecto antes de escribir código.

## Tareas

- Inspeccionar estructura existente.
- Detectar framework y versiones.
- Revisar `.env.example`.
- Revisar dependencias.
- Revisar configuración.
- Revisar migraciones existentes.
- Revisar modelos existentes.
- Revisar rutas.
- Revisar componentes Livewire.
- Revisar posibles conflictos.

Crear:

```text
ARCHITECTURE.md
```

Debe documentar:

- Arquitectura
- Módulos
- Entidades
- Relaciones
- Multiempresa
- Flujo de venta
- Flujo de inventario
- Flujo de caja
- Seguridad
- API
- Facturación electrónica futura

## Resultado esperado

Proyecto analizado y arquitectura documentada.

---

# FASE 1 — Base del sistema

## Objetivo

Construir la infraestructura fundamental.

## Implementar

- Configuración general
- Base de datos
- Migraciones
- Modelos base
- Traits necesarios
- Enums
- Manejo de excepciones
- Logging
- Estructura modular

Crear:

```text
Empresa
Sucursal
```

Preparar:

```text
empresa_id
sucursal_id
```

cuando corresponda.

## Testing

Verificar:

- Migraciones
- Relaciones
- Integridad referencial
- Creación de empresa
- Creación de sucursal

## Resultado

Sistema base funcional.

---

# FASE 2 — Autenticación, usuarios y permisos

## Objetivo

Crear el sistema de usuarios y autorización.

## Roles

```text
SUPER_ADMIN
ADMIN_EMPRESA
ADMIN_SUCURSAL
CAJERO
VENDEDOR
CONTADOR
```

## Implementar

- Login
- Logout
- Recuperación de contraseña
- Usuarios
- Roles
- Permisos
- Policies
- Gates
- Asociación usuario → empresa
- Asociación usuario → sucursal

## Permisos

Ejemplos:

```text
ventas.ver
ventas.crear
ventas.anular
ventas.devolver

productos.ver
productos.crear
productos.editar
productos.eliminar

inventario.ver
inventario.ajustar

caja.abrir
caja.cerrar

compras.ver
compras.crear

reportes.ver
```

## Testing

Probar que:

- Un usuario solo vea su empresa.
- Un usuario no pueda modificar otra empresa.
- Los roles funcionen.
- Los permisos funcionen.

---

# FASE 3 — Multiempresa

## Objetivo

Garantizar aislamiento de datos.

Todas las entidades de negocio deben asociarse correctamente a:

```text
empresa_id
```

Cuando aplique:

```text
sucursal_id
```

## Implementar

- Tenant/Company context
- Scopes
- Policies
- Middleware
- Validaciones
- Protección de consultas

## Regla crítica

Nunca confiar en:

```text
empresa_id
```

enviado directamente desde el frontend.

El backend debe determinar la empresa del usuario autenticado.

## Testing

Intentar acceder desde Empresa A a:

- Productos Empresa B
- Ventas Empresa B
- Clientes Empresa B
- Inventario Empresa B
- Cajas Empresa B

Todos deben ser rechazados.

---

# FASE 4 — Catálogos

## Objetivo

Crear la estructura de productos.

## Implementar

### Categorías

```text
categorias
```

### Marcas

```text
marcas
```

### Unidades

```text
unidades_medida
```

### Productos

```text
productos
```

Campos importantes:

```text
empresa_id
categoria_id
marca_id
unidad_medida_id
codigo
codigo_barras
nombre
descripcion
precio_compra
precio_venta
precio_mayorista
precio_distribuidor
stock
stock_minimo
iva
estado
```

## Preparar posteriormente

- Lotes
- Vencimientos
- Registro sanitario
- Principio activo
- Laboratorio
- Variantes

---

# FASE 5 — Inventario

## Objetivo

Crear un sistema de inventario basado en movimientos.

NO depender únicamente de:

```text
productos.stock
```

Crear:

```text
movimientos_inventario
```

## Tipos

```text
ENTRADA_COMPRA
SALIDA_VENTA
AJUSTE_POSITIVO
AJUSTE_NEGATIVO
DEVOLUCION_CLIENTE
DEVOLUCION_PROVEEDOR
TRASLADO
```

Registrar:

```text
producto
sucursal
cantidad
costo
stock_anterior
stock_posterior
referencia
usuario
fecha
```

## Implementar

- Entradas
- Salidas
- Ajustes
- Kardex
- Stock mínimo
- Alertas de stock

## Concurrencia

Utilizar:

```text
DB Transactions
Row Locks
```

cuando sea necesario.

Evitar:

- Stock negativo accidental
- Race conditions
- Actualizaciones simultáneas incorrectas

---

# FASE 6 — Proveedores y compras

## Proveedores

Implementar:

```text
proveedores
```

Datos:

- Nombre
- Documento
- Tipo documento
- Teléfono
- Email
- Dirección
- Estado

## Compras

Implementar:

```text
compras
compra_detalles
```

Flujo:

```text
Compra
   ↓
Detalles
   ↓
Actualizar inventario
   ↓
Kardex
   ↓
Cuenta por pagar
```

Todo el proceso crítico debe utilizar transacciones.

---

# FASE 7 — Clientes

## Implementar

```text
clientes
```

Datos:

- Nombre
- Documento
- Tipo documento
- Tipo persona
- Teléfono
- Email
- Dirección
- Estado

Crear cliente predeterminado:

```text
CONSUMIDOR FINAL
```

---

# FASE 8 — Crédito y cartera

## Implementar

```text
cuentas_por_cobrar
pagos_clientes
```

Funcionalidades:

- Venta a crédito
- Abonos
- Pagos
- Saldo
- Estado de cuenta
- Cartera vencida
- Historial de pagos

Flujo:

```text
Cliente
   ↓
Compra a crédito
   ↓
Cuenta por cobrar
   ↓
Abono
   ↓
Nuevo saldo
```

---

# FASE 9 — Caja

## Objetivo

Controlar completamente el dinero físico y electrónico.

## Implementar

```text
cajas
aperturas_caja
movimientos_caja
cierres_caja
```

## Funciones

- Apertura
- Fondo inicial
- Ventas
- Ingresos
- Egresos
- Devoluciones
- Cierre
- Arqueo
- Diferencias

Ejemplo:

```text
Fondo inicial       $100.000
Ventas efectivo     $850.000
Egresos             $50.000
--------------------------------
Esperado            $900.000

Contado             $895.000

Diferencia          -$5.000
```

Registrar cada movimiento.

---

# FASE 10 — Ventas

## Objetivo

Crear el motor transaccional de ventas.

Entidades:

```text
ventas
venta_detalles
```

Separar conceptualmente:

```text
Venta
DocumentoVenta
```

Una venta puede posteriormente generar:

```text
Ticket POS
Documento equivalente
Factura electrónica
```

## Flujo

```text
Cliente
   ↓
Productos
   ↓
Validar stock
   ↓
Calcular subtotal
   ↓
Calcular descuentos
   ↓
Calcular impuestos
   ↓
Calcular total
   ↓
Registrar venta
   ↓
Actualizar inventario
   ↓
Registrar caja
   ↓
Generar documento
```

Todo dentro de una transacción cuando corresponda.

---

# FASE 11 — POS

## Objetivo

Crear la interfaz principal de ventas.

Debe ser extremadamente rápida.

## Funciones

- Buscar producto
- Código de barras
- Scanner
- Carrito
- Cantidades
- Descuentos
- Impuestos
- Cliente
- Consumidor final
- Métodos de pago
- Efectivo
- Tarjeta
- Transferencia
- Pago mixto
- Cambio
- Finalizar venta
- Imprimir ticket

## UX

Optimizar para:

- Teclado
- Mouse
- Pantallas táctiles
- Lectores de códigos

Minimizar clics.

---

# FASE 12 — Métodos de pago

Implementar:

```text
EFECTIVO
TARJETA
TRANSFERENCIA
OTRO
MIXTO
```

Preparar posteriormente integraciones con:

- Wompi
- Mercado Pago
- Otros proveedores

No acoplar el POS directamente a un proveedor.

Crear una abstracción para pagos.

---

# FASE 13 — Listas de precios

Implementar:

```text
listas_precios
lista_precio_detalles
```

Ejemplo:

```text
Público
Mayorista
Distribuidor
Especial
```

Permitir seleccionar automáticamente el precio según:

- Cliente
- Tipo de cliente
- Lista asignada
- Usuario
- Configuración

---

# FASE 14 — Devoluciones

Implementar:

```text
devoluciones
devolucion_detalles
```

Permitir:

- Devolución total
- Devolución parcial

Actualizar:

```text
Inventario
Caja
Venta
Auditoría
```

Nunca eliminar una venta para realizar una devolución.

---

# FASE 15 — Reportes

Crear dashboard administrativo.

Indicadores:

```text
Ventas hoy
Ventas del mes
Productos vendidos
Ticket promedio
Utilidad
Clientes
Cartera
Stock bajo
```

Reportes:

- Ventas
- Compras
- Utilidad
- Inventario
- Kardex
- Caja
- Clientes
- Proveedores
- Cartera
- Cuentas por pagar
- Métodos de pago
- Impuestos

Utilizar filtros por:

```text
Fecha
Sucursal
Usuario
Producto
Categoría
Cliente
Proveedor
```

---

# FASE 16 — Auditoría

Crear:

```text
auditoria
```

Registrar:

- Usuario
- Empresa
- Acción
- Modelo
- Registro
- Datos anteriores
- Datos nuevos
- IP
- User Agent
- Fecha

Auditar especialmente:

- Ventas anuladas
- Devoluciones
- Cambios de precios
- Ajustes de inventario
- Aperturas/cierres de caja
- Cambios de permisos
- Eliminaciones

---

# FASE 17 — Impuestos

Crear módulo de impuestos.

Preparar:

```text
impuestos
```

Soportar inicialmente:

- IVA
- Exento
- Excluido

No codificar porcentajes directamente dentro de los modelos.

Los impuestos deben ser configurables.

---

# FASE 18 — Documentos comerciales

Separar:

```text
Venta
   ↓
DocumentoVenta
```

Preparar:

```text
documentos_venta
```

Tipos:

```text
TICKET
DOCUMENTO_EQUIVALENTE
FACTURA
```

Estados:

```text
BORRADOR
EMITIDO
ANULADO
```

Esto permitirá implementar facturación electrónica posteriormente sin rediseñar ventas.

---

# FASE 19 — Impresión

Preparar:

- Ticket 58 mm
- Ticket 80 mm
- PDF
- Facturas
- Comprobantes

No acoplar impresión directamente al POS.

Crear servicio independiente.

---

# FASE 20 — Notificaciones

Implementar arquitectura para:

- Stock bajo
- Productos próximos a vencer
- Caja cerrada
- Errores
- Eventos importantes

Utilizar:

```text
Events
Listeners
Jobs
Queues
```

cuando corresponda.

---

# FASE 21 — API

Crear:

```text
/api/v1
```

Endpoints para:

```text
Auth
Empresas
Sucursales
Productos
Inventario
Clientes
Proveedores
Ventas
Compras
Caja
Reportes
```

Utilizar:

```text
Laravel Sanctum
```

Aplicar las mismas reglas de autorización y aislamiento multiempresa de la interfaz web.

---

# FASE 22 — Facturación electrónica

Esta fase se implementará posteriormente.

NO realizar integración real con DIAN hasta que la arquitectura esté validada.

Preparar:

```text
facturas_electronicas
documentos_electronicos
resoluciones_facturacion
```

Preparar:

```text
FacturacionElectronicaService
```

La integración deberá soportar posteriormente:

- Factura electrónica
- Documento equivalente
- Nota crédito
- Nota débito
- XML
- PDF
- CUFE
- Resoluciones
- Prefijos
- Consecutivos
- Estados
- Respuestas del proveedor/DIAN

Arquitectura:

```text
Venta
   ↓
DocumentoVenta
   ↓
FacturacionElectronicaService
   ↓
Proveedor tecnológico / DIAN
   ↓
Respuesta
   ↓
XML
PDF
CUFE
Estado
```

La facturación electrónica NO debe contenerse dentro del controlador del POS.

---

# FASE 23 — Droguerías

Esta fase puede desarrollarse posteriormente.

Preparar:

```text
lotes
vencimientos
registros_sanitarios
principios_activos
laboratorios
```

Alertas:

```text
Productos próximos a vencer
Productos vencidos
Stock mínimo
```

---

# FASE 24 — Ferreterías

Implementar capacidades específicas:

- Venta por metro
- Venta por unidad
- Venta por caja
- Venta por kilogramo
- Rollos
- Bultos
- Conversiones de unidades

Ejemplo:

```text
1 caja = 100 unidades
1 rollo = 50 metros
```

El inventario debe actualizar correctamente la unidad base.

---

# FASE 25 — Multisucursal

Una empresa podrá tener:

```text
Empresa
├── Sucursal Centro
├── Sucursal Norte
└── Sucursal Sur
```

Cada sucursal puede tener:

- Inventario
- Cajas
- Usuarios
- Ventas
- Compras

Implementar posteriormente:

- Traslados
- Inventario por sucursal
- Reportes por sucursal
- Usuarios por sucursal

---

# FASE 26 — SaaS

Convertir el sistema en producto comercial.

Implementar:

```text
Planes
Suscripciones
Clientes SaaS
Límites
Facturación del servicio
```

Ejemplo:

```text
PLAN BÁSICO

1 sucursal
2 usuarios
POS
Inventario
Caja
```

```text
PLAN PROFESIONAL

3 sucursales
10 usuarios
POS
Inventario
Compras
Cartera
Reportes
```

```text
PLAN EMPRESARIAL

Sucursales ilimitadas
Usuarios ilimitados
API
Facturación electrónica
Reportes avanzados
```

No fijar precios directamente en código.

---

# FASE 27 — PWA / Operación offline

Evaluar posteriormente una arquitectura PWA.

Objetivo:

```text
Internet
    ↓
Servidor

Internet caída
    ↓
POS local
    ↓
Venta offline
    ↓
Internet vuelve
    ↓
Sincronización
```

Esta fase requiere análisis profundo de:

- Sincronización
- Conflictos
- Identificadores
- Inventario
- Ventas
- Cajas

NO implementar sin diseñar primero la estrategia de sincronización.

---

# FASE 28 — Testing integral

Crear pruebas:

### Unitarias

- Cálculos
- Impuestos
- Descuentos
- Conversión de unidades

### Feature

- Login
- Productos
- Compras
- Ventas
- Caja
- Clientes
- Inventario

### Seguridad

- Multiempresa
- Roles
- Permisos

### Integridad

- Stock
- Caja
- Ventas
- Devoluciones

### Concurrencia

Probar dos ventas simultáneas del mismo producto.

---

# FASE 29 — Optimización

Analizar:

- Consultas lentas
- Índices
- N+1 queries
- Cache
- Redis
- Jobs
- Colas
- Paginación

Optimizar solamente después de identificar problemas reales.

No realizar optimizaciones prematuras que compliquen el código.

---

# FASE 30 — Producción

Preparar:

```text
Ubuntu
Nginx
PHP-FPM
MySQL
Redis
Supervisor
SSL
Cloudflare
```

Configurar:

- Variables de entorno
- Logs
- Queue workers
- Scheduler
- Backups
- SSL
- Seguridad
- Monitoreo

---

# 6. Reglas estrictas para Antigravity

Antes de modificar código:

```text
1. Inspeccionar.
2. Analizar.
3. Planificar.
4. Implementar.
5. Probar.
6. Corregir.
7. Documentar.
```

Nunca:

- Crear archivos innecesarios.
- Duplicar lógica.
- Saltarse migraciones.
- Ignorar errores.
- Colocar lógica empresarial en Blade.
- Confiar en `empresa_id` enviado por frontend.
- Eliminar ventas directamente.
- Actualizar inventario sin registrar movimiento.
- Modificar stock sin transacción cuando exista riesgo de concurrencia.
- Mezclar facturación electrónica con lógica del POS.

---

# 7. Regla de avance

Después de completar cada fase, generar un registro:

```text
PROGRESS.md
```

Ejemplo:

```text
# Progreso

## Fase 1
Estado: COMPLETADA

Migraciones: OK
Modelos: OK
Tests: OK
Documentación: OK

## Fase 2
Estado: EN PROGRESO

Usuarios: OK
Roles: OK
Permisos: PENDIENTE
Tests: PENDIENTE
```

Nunca marcar una fase como completada si existen errores críticos.

---

# 8. Definición de "completado"

Una fase solamente está COMPLETADA cuando:

- Código implementado.
- Migraciones funcionando.
- Relaciones funcionando.
- Validaciones funcionando.
- Autorización funcionando.
- Multiempresa validada.
- Tests ejecutados.
- Errores corregidos.
- Documentación actualizada.
- No rompe funcionalidades anteriores.

---

# 9. Prioridad

La prioridad del sistema es:

```text
1. Integridad de datos
2. Seguridad
3. Multiempresa
4. Arquitectura
5. Mantenibilidad
6. Rendimiento
7. UX
8. Escalabilidad
```

---

# 10. Principio final

Este proyecto no debe ser tratado como un proyecto CRUD.

Debe ser tratado como un **producto comercial real**.

Cada decisión debe considerar:

- Seguridad
- Datos financieros
- Inventario
- Auditoría
- Concurrencia
- Multiempresa
- Escalabilidad
- Mantenimiento
- Experiencia del usuario
- Futura facturación electrónica

La arquitectura debe permitir crecer sin tener que reescribir el sistema completo.

---

# INSTRUCCIÓN PARA ANTIGRAVITY

Cuando este archivo esté disponible en el proyecto:

1. Leer completamente `ROADMAP.md`.
2. Leer `ARCHITECTURE.md`.
3. Identificar la fase actual.
4. No saltar fases.
5. Implementar únicamente la fase autorizada.
6. Ejecutar pruebas.
7. Corregir errores.
8. Actualizar documentación.
9. Actualizar `PROGRESS.md`.
10. Informar qué fue implementado y qué quedó pendiente.

Si una decisión arquitectónica importante no está definida, detener la implementación de esa parte, analizar alternativas y documentar la decisión antes de continuar.

**No generar código masivo sin planificación.**

**No modificar módulos funcionales sin verificar dependencias.**

**No eliminar funcionalidades existentes para facilitar una implementación.**

**No considerar una funcionalidad terminada hasta que haya sido probada.**

Comenzar siempre por la fase indicada en `PROGRESS.md`.

Si `PROGRESS.md` no existe, comenzar por:

**FASE 0 — Análisis y arquitectura.**