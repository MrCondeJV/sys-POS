# Fase A0 — Diagnóstico de Cobertura de Código y Motor de Base de Datos

## 1. Estado de Extensiones de Cobertura (`pcov` / `xdebug`) en Entorno Local
- **Comando verificado:** `php -m` (PHP 8.4.26 en Windows)
- **Resultado:** Ni `pcov` ni `xdebug` están instalados como DLL en el binario local de PHP en Windows.
- **Cómo habilitar cobertura localmente en Windows:**
  1. Descargar `php_pcov.dll` para PHP 8.4 TS x64 desde PECL (`https://pecl.php.net/package/pcov`).
  2. Copiar `php_pcov.dll` en la carpeta `ext/` de PHP y añadir en `php.ini`:
     ```ini
     extension=pcov
     pcov.enabled=1
     pcov.directory=app
     ```
  3. Ejecutar:
     ```powershell
     php artisan test --coverage --min=80
     ```
- **Habilitación en CI:** El archivo `.github/workflows/ci.yml` configura `coverage: pcov` en `shivammathur/setup-php@v2` para que GitHub Actions disponga de cobertura nativa en cada push/PR.

## 2. Detección de Motor de Base de Datos
- **En `.env.example`:** `DB_CONNECTION=sqlite`
- **En `phpunit.xml`:** `DB_CONNECTION=sqlite` con `DB_DATABASE=:memory:`
- **En `config/database.php`:** Soporta `sqlite`, `mysql`, `mariadb`, `pgsql` y `sqlsrv`.
- **En el código (`app/Actions/`):** Se utiliza `->lockForUpdate()` en transacciones críticas (`RegistrarVentaAction`, `RegistrarMovimientoInventarioAction`, `RegistrarAbonoCarteraAction`).
- **Implicación técnica documentada:**
  - En SQLite, `lockForUpdate()` no aplica bloqueo a nivel de fila (`row-level lock`), sino bloqueo de escritura a nivel de archivo/base de datos.
  - Por ello, las pruebas de concurrencia real (`tests/Concurrency/BloqueoConcurrenciaRealTest.php`, grupo `concurrency`) se dejaron configuradas contra un servicio **MySQL 8.0** en `.github/workflows/ci.yml` y se consultó al usuario en `PREGUNTAS-PENDIENTES.md` el motor definitivo de producción.
