<?php

namespace App\Enums;

enum PermisoSistema: string
{
    // Ventas
    case VENTAS_VER = 'ventas.ver';
    case VENTAS_CREAR = 'ventas.crear';
    case VENTAS_ANULAR = 'ventas.anular';
    case VENTAS_DEVOLVER = 'ventas.devolver';

    // Productos
    case PRODUCTOS_VER = 'productos.ver';
    case PRODUCTOS_CREAR = 'productos.crear';
    case PRODUCTOS_EDITAR = 'productos.editar';
    case PRODUCTOS_ELIMINAR = 'productos.eliminar';

    // Inventario
    case INVENTARIO_VER = 'inventario.ver';
    case INVENTARIO_AJUSTAR = 'inventario.ajustar';

    // Caja
    case CAJA_VER = 'caja.ver';
    case CAJA_ADMINISTRAR = 'caja.administrar';
    case CAJA_ABRIR = 'caja.abrir';
    case CAJA_CERRAR = 'caja.cerrar';
    case CAJA_MOVIMIENTO = 'caja.movimiento';

    // Compras
    case COMPRAS_VER = 'compras.ver';
    case COMPRAS_CREAR = 'compras.crear';
    case COMPRAS_ANULAR = 'compras.anular';

    // Proveedores
    case PROVEEDORES_VER = 'proveedores.ver';
    case PROVEEDORES_CREAR = 'proveedores.crear';
    case PROVEEDORES_EDITAR = 'proveedores.editar';
    case PROVEEDORES_ELIMINAR = 'proveedores.eliminar';

    // Clientes
    case CLIENTES_VER = 'clientes.ver';
    case CLIENTES_CREAR = 'clientes.crear';
    case CLIENTES_EDITAR = 'clientes.editar';
    case CLIENTES_ELIMINAR = 'clientes.eliminar';

    // Cartera y Cuentas por Cobrar
    case CARTERA_VER = 'cartera.ver';
    case CARTERA_CREAR = 'cartera.crear';
    case CARTERA_ABONAR = 'cartera.abonar';
    case CARTERA_ANULAR = 'cartera.anular';

    // Reportes
    case REPORTES_VER = 'reportes.ver';

    // Empresas y Sucursales
    case EMPRESA_GESTIONAR = 'empresa.gestionar';
    case SUCURSALES_GESTIONAR = 'sucursales.gestionar';

    // Usuarios
    case USUARIOS_VER = 'usuarios.ver';
    case USUARIOS_GESTIONAR = 'usuarios.gestionar';

    // Listas de Precios
    case LISTAS_PRECIOS_VER = 'listas_precios.ver';
    case LISTAS_PRECIOS_CREAR = 'listas_precios.crear';
    case LISTAS_PRECIOS_EDITAR = 'listas_precios.editar';
    case LISTAS_PRECIOS_ELIMINAR = 'listas_precios.eliminar';

    /**
     * Retorna todos los valores como un array plano de strings.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
