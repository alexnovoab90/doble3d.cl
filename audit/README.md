# Auditoría reproducible de doble3d.cl

Esta carpeta contiene utilidades de solo lectura para registrar el estado público del sitio y preparar respaldos antes de cualquier cambio en producción.

## Capturar la línea base

Desde la raíz del repositorio:

```powershell
powershell -ExecutionPolicy Bypass -File audit/scripts/capture-public-baseline.ps1
powershell -ExecutionPolicy Bypass -File audit/scripts/export-editorial-inventory.ps1
```

Los resultados se guardan en `audit/baseline/` con fecha y hora y se excluyen de Git porque son evidencia operativa variable. La captura incluye estados HTTP, títulos, metadescripciones e inventario editorial; no usa credenciales ni realiza escrituras remotas.

## Respaldos y manifiesto

Los respaldos descargados se guardan en `audit/backups/<timestamp>/` y también se excluyen de Git. Para generar hashes, tamaños y comandos de restauración sin credenciales reales:

```powershell
powershell -ExecutionPolicy Bypass -File audit/scripts/new-backup-manifest.ps1 -BackupDir audit/backups/<timestamp>
```

Antes de cualquier escritura remota deben existir y validarse:

- copia de `.htaccess`;
- tema activo completo;
- `image-gen.php` y `media-gen.php`;
- export SQL completo de la base de datos WordPress;
- `MANIFEST.md` con archivos no vacíos y hashes SHA-256.

El respaldo de archivos del 18 de julio de 2026 contiene 39 archivos (605.863 bytes). El export SQL todavía no está disponible: por este motivo **toda escritura remota permanece bloqueada**.

No se deben guardar contraseñas, claves de aplicación ni cookies en esta carpeta o en Git.

