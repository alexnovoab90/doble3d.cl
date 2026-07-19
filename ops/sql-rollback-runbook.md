# Runbook de rollback SQL de WordPress

## Respaldo base

El dump completo está en `audit/backups/20260718-181139/database/ddcl_wp950-before-remediation.sql`. Es sensible, no debe agregarse a Git ni copiarse a `public_html`. Antes de usarlo se debe verificar su tamaño, su SHA-256 y la presencia de las tablas de posts, metadatos y opciones.

## Rollback puntual por lote

Antes de cada escritura REST se guarda en `audit/baseline/wordpress-rollback/<fecha-utc>/` un JSON con ID, URL, slug, estado, título y metadatos SEO actuales. La reversión envía únicamente esos valores previos al mismo recurso. Después se comprueba HTTP 200, URL sin cambios y HTML público.

## Restauración completa de emergencia

La importación completa solo se usa si la reversión puntual no recupera el sitio. Primero se restaura el dump en una base de staging y se valida WordPress. Restaurarlo directamente en producción puede sobrescribir cambios posteriores al respaldo y requiere una nueva exportación inmediatamente anterior a la operación.

## Evidencia mínima

Cada lote registra fecha UTC, IDs afectados, valores antes/después, respuestas HTTP, verificación pública y decisión de continuar o revertir.
