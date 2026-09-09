<?php

namespace App\Support\Tenancy;

use App\Models\Empresa;

class CompanyContext
{
    protected static ?int $companyId = null;

    protected static ?Empresa $company = null;

    /**
     * Establecer el ID de la empresa activa en el contexto actual.
     */
    public static function setCompanyId(?int $id): void
    {
        static::$companyId = $id;
        if ($id === null) {
            static::$company = null;
        }
    }

    /**
     * Establecer la instancia de la empresa activa.
     */
    public static function setCompany(?Empresa $company): void
    {
        static::$company = $company;
        static::$companyId = $company?->id;
    }

    /**
     * Obtener el ID de la empresa activa.
     */
    public static function getId(): ?int
    {
        return static::$companyId;
    }

    /**
     * Obtener el modelo de la empresa activa.
     */
    public static function getCompany(): ?Empresa
    {
        if (static::$company === null && static::$companyId !== null) {
            static::$company = Empresa::find(static::$companyId);
        }

        return static::$company;
    }

    /**
     * Verificar si existe un contexto de empresa activo.
     */
    public static function check(): bool
    {
        return static::$companyId !== null;
    }

    /**
     * Limpiar el contexto de empresa.
     */
    public static function clear(): void
    {
        static::$companyId = null;
        static::$company = null;
    }

    /**
     * Ejecutar una acción dentro del contexto de una empresa temporal y restaurar el contexto previo.
     */
    public static function runInContext(int|Empresa $company, callable $callback): mixed
    {
        $previousId = static::$companyId;
        $previousCompany = static::$company;

        try {
            if ($company instanceof Empresa) {
                static::setCompany($company);
            } else {
                static::setCompanyId($company);
            }

            return $callback();
        } finally {
            static::$companyId = $previousId;
            static::$company = $previousCompany;
        }
    }
}
