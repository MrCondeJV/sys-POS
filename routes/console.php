<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Programación del Backup de Base de Datos y Google Drive
// Se ejecuta cada 6 horas
Schedule::command('app:backup-databases')->everySixHours();
