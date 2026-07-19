# Diseño de despliegue seguro en cPanel y rollback SQL

## Objetivo

Convertir el repositorio de Doble 3D en una fuente versionada y verificable del tema activo, habilitar un primer despliegue no público mediante cPanel y preparar la reversión de los cambios WordPress almacenados en base de datos.

## Decisión de arquitectura

Se versionará exclusivamente el tema activo en `wordpress/theme/doble3d/`. El primer `.cpanel.yml` copiará ese directorio a `/home50/ddcl/deploy-staging/doble3d-theme/`, fuera de `public_html`. No se versionarán ni desplegarán WordPress core, `wp-config.php`, uploads, caché, logs, credenciales ni bases de datos.

El dump SQL completo continuará en `audit/backups/`, ignorado por Git. La reversión normal será puntual por lote usando capturas de valores previos; la importación completa del dump quedará reservada para una emergencia y deberá ejecutarse primero en staging cuando exista una base descartable.

## Alternativas descartadas

1. **Despliegue directo a `public_html`:** reduce pasos, pero una ruta o copia incorrecta podría afectar producción antes de comprobar el paquete.
2. **Repositorio de WordPress completo:** incluiría estado dinámico que no pertenece al control de versiones y aumentaría el riesgo de filtrar datos o sobrescribir uploads.

## Componentes

### Fuente del tema

- Origen verificado: `audit/backups/20260718-181139/public_html/wp-content/themes/doble3d/`.
- Destino versionado: `wordpress/theme/doble3d/`.
- Debe conservar los 36 archivos y sus bytes originales antes de cualquier modificación funcional.
- Un manifiesto SHA-256 permitirá cotejar el snapshot versionado con el respaldo FTP.

### Contrato de despliegue

`.cpanel.yml` deberá:

- declarar YAML válido para cPanel;
- crear `/home50/ddcl/deploy-staging/doble3d-theme/`;
- copiar únicamente `wordpress/theme/doble3d/`;
- no mencionar `public_html`, rutas de base de datos, uploads ni archivos de configuración sensibles;
- no eliminar archivos ni ejecutar migraciones.

### Validación automatizada

Un test Node sin dependencias comprobará:

- presencia de archivos esenciales del tema;
- ausencia de patrones y nombres sensibles;
- destino exacto de staging;
- inexistencia de comandos destructivos;
- correspondencia de los hashes del tema con el manifiesto.

### Rollback SQL

El dump validado es `ddcl_wp950-before-remediation.sql`, de 6.990.049 bytes, con 42 tablas y las tablas esenciales de WordPress presentes. Permanecerá fuera de Git.

Antes de cada lote remoto se guardará, en un directorio ignorado:

- fecha UTC;
- identificador de página o entrada;
- URL y slug;
- título y estado actuales;
- metadatos SEO actuales;
- hash del dump base;
- payload inverso para restaurar solo los campos modificados.

No se construirá un SQL con valores sensibles dentro del repositorio. El runbook versionado solo documentará procedimientos y rutas con marcadores seguros.

## Flujo gradual

1. Copiar el tema respaldado al área versionada y comprobar igualdad SHA-256.
2. Hacer fallar el test de contrato por ausencia de `.cpanel.yml`.
3. Implementar `.cpanel.yml` con destino exclusivo de staging.
4. Ejecutar los tests editoriales, n8n y de despliegue.
5. Integrar en `main` y publicar GitHub.
6. Actualizar cPanel y desplegar únicamente a staging.
7. Comparar el staging con el snapshot mediante hashes.
8. Preparar las capturas de rollback de base de datos antes de cualquier lote SEO.

## Recuperación

- Un fallo de despliegue en esta fase no afecta producción porque el destino está fuera de `public_html`.
- El tema original permanece en el respaldo FTP con manifiesto SHA-256.
- El dump SQL completo permite recuperación de emergencia.
- Los cambios SEO y editoriales posteriores se revertirán primero mediante payloads puntuales para no sobrescribir contenido creado después del backup.

## Criterios de aceptación

- El tema versionado coincide byte por byte con el respaldo de producción.
- El escaneo de secretos devuelve cero coincidencias.
- `.cpanel.yml` no puede escribir en `public_html`.
- Las pruebas existentes y el nuevo contrato pasan.
- El SQL y los artefactos de rollback siguen ignorados por Git.
- Ninguna acción de esta fase modifica el sitio público ni la base de datos de producción.
