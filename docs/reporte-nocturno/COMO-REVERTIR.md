# Guía de Reversión Rápida (`COMO-REVERTIR.md`)

Todo el trabajo nocturno se realizó en la rama aislada `noche/auditoria-y-rediseno` sin tocar `main` ni hacer `git push`.

## 1. Volver al estado exacto previo a la ejecución nocturna (`main`)
```powershell
git checkout main
```

## 2. Volver al estado previo a la Fase 4 de migración de vistas (`pre-fase-4`)
```powershell
git checkout pre-fase-4
```

## 3. Puntos de Control (`Tags`) en la rama `noche/auditoria-y-rediseno`

| Tag | Etapa | Comando para volver a este punto en la rama |
| :--- | :--- | :--- |
| `pre-fase-4` | Fin de Fases 0–3 (Componentes base y `app.css`) | `git reset --hard pre-fase-4` |
| `noche-checkpoint-0` | Inicio de sesión nocturna (Lotes 1, 2 y 3A + suite base) | `git reset --hard noche-checkpoint-0` |
| `noche-checkpoint-1B` | Cierre de Parte 1 y Parte 1B (QA Lote 3A, PDFs, Capturas, CI) | `git reset --hard noche-checkpoint-1B` |
| `noche-checkpoint-2` | Cierre de Parte 2 (Auditoría A0–A9, Multiempresa, Roles, Integridad) | `git reset --hard noche-checkpoint-2` |
| `noche-checkpoint-lote4` | Parte 3 — Lote 4: Módulo `ventas/*` | `git reset --hard noche-checkpoint-lote4` |
| `noche-checkpoint-lote5` | Parte 3 — Lote 5: `productos/*`, `inventario/*`, `traslados/*` | `git reset --hard noche-checkpoint-lote5` |
| `noche-checkpoint-lote6` | Parte 3 — Lote 6: `cartera/*`, `clientes/*` | `git reset --hard noche-checkpoint-lote6` |
| `noche-checkpoint-lote7` | Parte 3 — Lote 7: `compras/*`, `proveedores/*` | `git reset --hard noche-checkpoint-lote7` |
| `noche-checkpoint-lote8` | Parte 3 — Lote 8: `reportes/*` | `git reset --hard noche-checkpoint-lote8` |
| `noche-checkpoint-lote9` | Parte 3 — Lote 9: Resto de módulos (`sucursales`, `catalogos`, `impuestos`, `listas-precios`, `devoluciones`, `documentos`, `facturacion-electronica`, `notificaciones`, `auditoria`, `impresion`) | `git reset --hard noche-checkpoint-lote9` |
| `noche-checkpoint-lote3B` | Parte 3 — Lote 3B: Terminal `pos/*` | `git reset --hard noche-checkpoint-lote3B` |
| `noche-checkpoint-4` | Cierre de Parte 4 (Diseño Wompi B0 e Informe Final) | `git reset --hard noche-checkpoint-4` |

## 4. Restaurar copia de seguridad de `database.sqlite` (si fuera necesario)
Aunque `database/database.sqlite` no fue modificada durante la sesión nocturna, existe copia de respaldo en:
```powershell
Copy-Item "docs/reporte-nocturno/backup/database.sqlite.bak" "database/database.sqlite" -Force
```
