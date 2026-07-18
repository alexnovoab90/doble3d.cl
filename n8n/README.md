# Workflow editorial de Doble 3D

`auto-post-doble3d.json` es el único export maestro importable. Los exports históricos separados por martes y jueves se conservan en `archive/` únicamente como referencia y no deben importarse junto al maestro.

## Estado seguro del piloto

- El workflow exportado permanece inactivo.
- Toda publicación se crea como borrador (`draft`).
- Martes y jueves comparten una sola programación a las 11:00, zona `America/Santiago`.
- Antes de generar contenido se valida el nicho y se buscan temas similares.
- Después de redactar se validan estructura, metadatos, fuentes, FAQ, enlaces comerciales y duplicados.
- Se permite una sola solicitud de corrección; un segundo fallo detiene la ejecución.
- Se vuelve a comprobar el slug inmediatamente antes de crear medios o borradores.
- Las operaciones HTTP idempotentes reintentan hasta tres veces con la espera fija de 5 segundos que ofrece n8n. Los POST que crean medios o borradores no se reintentan a ciegas: un timeout puede ocurrir después de que WordPress haya creado el recurso y repetirlo causaría duplicados.
- Los fallos terminales se delegan a `error-handler-doble3d.json`, que envía sólo datos operativos mínimos y nunca cuerpos completos ni valores de autenticación.

## Verificación local

```powershell
node --test n8n/tests/policy.test.mjs n8n/tests/workflow-contract.test.mjs
```

El script `scripts/consolidate-workflow.mjs` reconstruye de manera determinista las compuertas del export maestro existente. No lo ejecutes sobre una exportación distinta sin revisar previamente el diff.

Importa primero `error-handler-doble3d.json`, configura su credencial SMTP y confirma que su ID coincida con `settings.errorWorkflow` del maestro. La activación y la persistencia como servicio se realizan en fases posteriores. Mientras falte un respaldo SQL validado, no se debe importar ni activar este workflow en producción.

El backoff variable 5/15/30 segundos del diseño inicial no puede expresarse con la opción nativa `Retry on Fail`, que usa un intervalo fijo. Para evitar duplicados, este export prefiere reintentos fijos únicamente en operaciones seguras y deja los POST de creación sin reintento automático.
