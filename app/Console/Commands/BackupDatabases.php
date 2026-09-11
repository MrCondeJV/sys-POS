<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Empresa;
use Illuminate\Support\Facades\Log;

class BackupDatabases extends Command
{
    /**
     * El nombre y la firma del comando de consola.
     *
     * @var string
     */
    protected $signature = 'app:backup-databases';

    /**
     * La descripción del comando de consola.
     *
     * @var string
     */
    protected $description = 'Realiza un backup de la base de datos (Single Database Multi-tenant) y lo sincroniza con Google Drive';

    /**
     * Ejecuta el comando de consola.
     */
    public function handle()
    {
        // 1. Directorio Permanente fuera de la carpeta del proyecto por seguridad
        // Nota: Asegúrate de que el usuario que ejecuta PHP tenga permisos en esta ruta
        $serverDir = '/home/mambacode/backups_pos_comercial_mambacode';
        
        // En Windows para pruebas locales, podrías usar algo como:
        if (PHP_OS_FAMILY === 'Windows') {
            $serverDir = storage_path('backups');
        }

        if (!is_dir($serverDir)) {
            mkdir($serverDir, 0755, true);
        }

        // Obtener credenciales desde la conexión mysql (arquitectura single-db)
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");
        
        $user = $config['username'] ?? env('DB_USERNAME');
        $pass = $config['password'] ?? env('DB_PASSWORD');
        $host = $config['host'] ?? env('DB_HOST');
        $dbName = $config['database'] ?? env('DB_DATABASE');

        $this->info("Iniciando proceso de backup (Local y Drive)...");
        $this->comment("Arquitectura detectada: Single Database Multi-tenant");

        // 2. Backup de la Base de Datos Principal
        // Esta base de datos contiene todas las tablas con segregación por empresa_id
        $this->doBackup($dbName, 'pos-comercial-mambacode', $serverDir, $user, $pass, $host);

        // 3. Sincronizar con Google Drive en una carpeta específica para este proyecto
        $this->info("Sincronizando con Google Drive (Carpeta: backups_pos_comercial_mambacode)...");
        
        // El comando rclone requiere que rclone esté instalado y configurado con el remote 'gdrive'
        $command = "rclone copy \"$serverDir\" gdrive:backups_pos_comercial_mambacode";
        
        $output = [];
        $resultCode = 0;
        exec($command, $output, $resultCode);

        if ($resultCode === 0) {
            $this->info("¡Backup completado! Archivos guardados en: $serverDir y en Drive.");
        } else {
            $this->warn("Nota: rclone falló o no está configurado. El backup local se realizó correctamente.");
            if (!empty($output)) {
                Log::error("Fallo rclone backup: " . implode("\n", $output));
            }
        }
    }

    /**
     * Ejecuta el comando mysqldump para una base de datos específica.
     */
    private function doBackup($dbName, $label, $dir, $user, $pass, $host)
    {
        // Al quitar la fecha, el archivo se sobrescribirá en cada ejecución,
        // manteniendo solo el backup más reciente y evitando la acumulación de archivos.
        $fileName = "{$label}.sql";
        $filePath = "$dir/$fileName";

        $this->comment("Procesando: $dbName -> $fileName");
        
        // El parámetro --no-tablespaces suele ser necesario en algunos entornos para evitar errores de permisos
        // Agregamos comillas a los parámetros para manejar caracteres especiales
        $cmd = "mysqldump --user=\"$user\" --password=\"$pass\" --host=\"$host\" --no-tablespaces $dbName > \"$filePath\" 2>&1";
        
        $output = [];
        $resultCode = 0;
        exec($cmd, $output, $resultCode);

        if ($resultCode !== 0) {
            $this->error("Error al respaldar la DB: $dbName");
            $this->error("Salida: " . implode("\n", $output));
            Log::error("Error mysqldump DB: $dbName. Output: " . implode("\n", $output));
        } else {
            $this->info("Respaldo exitoso: $fileName");
        }
    }
}
