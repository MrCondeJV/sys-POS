<?php

namespace App\Support\Tenancy;

use App\Models\Sucursal;

class BranchContext
{
    protected static ?int $branchId = null;

    protected static ?Sucursal $branch = null;

    /**
     * Establece el ID de la sucursal activa en el contexto.
     */
    public static function setId(?int $id): void
    {
        static::$branchId = $id;
        static::$branch = null;
        if (session()->isStarted()) {
            session(['sucursal_activa_id' => $id]);
        }
    }

    public static function setBranchId(?int $id): void
    {
        static::setId($id);
    }

    /**
     * Obtiene el ID de la sucursal activa.
     */
    public static function getId(): ?int
    {
        if (static::$branchId === null && session()->isStarted()) {
            static::$branchId = session('sucursal_activa_id');
        }

        return static::$branchId;
    }

    /**
     * Obtiene el modelo de la sucursal activa.
     */
    public static function getBranch(): ?Sucursal
    {
        $id = static::getId();
        if ($id && (static::$branch === null || static::$branch->id !== $id)) {
            static::$branch = Sucursal::find($id);
        }

        return static::$branch;
    }

    /**
     * Verifica si hay una sucursal activa seleccionada.
     */
    public static function check(): bool
    {
        return static::getId() !== null;
    }

    /**
     * Limpia el contexto de sucursal.
     */
    public static function clear(): void
    {
        static::$branchId = null;
        static::$branch = null;
        if (session()->isStarted()) {
            session()->forget('sucursal_activa_id');
        }
    }
}
