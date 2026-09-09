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
    case CAJA_ABRIR = 'caja.abrir';
    case CAJA_CERRAR = 'caja.cerrar';

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

    // Reportes
    case REPORTES_VER = 'reportes.ver';

    // Empresas y Sucursales
    case EMPRESA_GESTIONAR = 'empresa.gestionar';
    case SUCURSALES_GESTIONAR = 'sucursales.gestionar';

    // Usuarios
    case USUARIOS_VER = 'usuarios.ver';
    case USUARIOS_GESTIONAR = 'usuarios.gestionar';

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
