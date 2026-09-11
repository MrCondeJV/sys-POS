<?php

namespace App\Console\Commands;

use App\Models\Empresa;
use App\Support\Tenancy\CompanyContext;
use Database\Seeders\FerreteriaClientesSeeder;
use Database\Seeders\FerreteriaProductosSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SeedCompanyData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:seed-company-data {empresa_id : El ID de la empresa a la que se le cargarán los datos de prueba}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Carga datos de prueba (productos y clientes) para una empresa específica.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $empresaId = $this->argument('empresa_id');

        $this->info("Iniciando carga de datos de prueba para la empresa ID: {$empresaId}...");

        $empresa = Empresa::find($empresaId);

        if (! $empresa) {
            $this->error("No se encontró ninguna empresa con el ID: {$empresaId}");

            return Command::FAILURE;
        }

        // Establecer el contexto de la empresa
        CompanyContext::setCompany($empresa);
        $this->comment("Contexto de empresa establecido para: {$empresa->nombre_comercial} (ID: {$empresa->id})");

        try {
            // Ejecutar seeders específicos para la empresa
            $this->info('Sembrando productos de ferretería...');
            (new FerreteriaProductosSeeder)->run($empresa);
            $this->info('Productos sembrados correctamente.');

            $this->info('Sembrando clientes de ferretería...');
            (new FerreteriaClientesSeeder)->run($empresa);
            $this->info('Clientes sembrados correctamente.');

            $this->info("¡Datos de prueba cargados exitosamente para la empresa: {$empresa->nombre_comercial}!");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Ocurrió un error al cargar los datos de prueba: {$e->getMessage()}");
            Log::error("Error al sembrar datos para empresa {$empresaId}: {$e->getMessage()}");

            return Command::FAILURE;
        } finally {
            // Limpiar el contexto de la empresa al finalizar
            CompanyContext::clear();
        }
    }
}

/*
*  php artisan app:seed-company-data 1  Donde 1 es el empresa_id de la empresa a la que quieres cargar los datos de prueba.
*/
    