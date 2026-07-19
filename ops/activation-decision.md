# Decisión de activación automática

Estado: **CONTINUE_DRAFT — BLOQUEADO**

Fecha de evaluación: 2026-07-18

## Evidencia disponible

- Workflow maestro local consolidado, inactivo y configurado para `draft`.
- Suite local completa en verde.
- Servicio n8n persistente y saludable, con cero workflows activos.
- Matriz editorial, metadatos, hardening FTP y tres artículos pilares preparados localmente.
- Export SQL completo y validado, ignorado por Git, con runbook de rollback puntual por lote.

## Condiciones aún no cumplidas

- Las correcciones remotas de metadatos, títulos y artefactos no se han aplicado.
- Los tres pilares no existen aún como borradores revisados en WordPress.
- El manejador de errores no está importado/configurado con SMTP.
- No se han generado ni revisado cuatro borradores programados durante dos semanas.

## Decisión

No se autoriza `STATUS='publish'` ni la activación del schedule maestro. El sistema debe permanecer en `draft` y los workflows locales deben seguir inactivos hasta completar todos los gates.

Cuando el piloto termine, la aprobación debe incluir cuatro filas aceptables en `pilot-log.csv`, cero defectos críticos, cero duplicados, cero temas fuera de nicho, cero cifras sin fuente y cero errores no manejados. Sólo entonces se ejecutará la prueba con `AUTOMATION_APPROVED=true`, se actualizará este documento con aprobador y fecha, y se guardará el snapshot final.

## Rollback automático

Cualquier duplicado, tema fuera de nicho, fuente ausente, metadato inválido o error terminal no manejado obliga a volver a `draft` antes de la siguiente programación y reinicia una nueva ventana de cuatro borradores.
