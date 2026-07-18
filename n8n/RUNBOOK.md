# Runbook de n8n para Doble 3D

Este procedimiento mantiene n8n bajo la cuenta actual de Windows, conserva el directorio de datos existente y evita depender de una consola abierta. No registres ni inicies el servicio hasta confirmar que todos los workflows capaces de escribir en WordPress están inactivos o en modo `draft`.

## Iniciar y registrar

Primero verifica qué workflows están activos en la instancia. Cuando sea seguro, abre PowerShell con la cuenta operativa y ejecuta:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\register-n8n-task.ps1 -WhatIf
powershell -ExecutionPolicy Bypass -File .\scripts\register-n8n-task.ps1
Start-ScheduledTask -TaskName 'Doble3D-n8n'
```

`-WhatIf` muestra el cambio sin registrarlo. La tarea usa privilegios limitados, arranca al iniciar sesión y reintenta el proceso hasta tres veces si termina.

## Salud

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\healthcheck.ps1
```

El resultado esperado es `n8n healthy`. `iniciar-n8n.bat` ejecuta esta comprobación, inicia la tarea si hace falta, espera 15 segundos, comprueba de nuevo y sólo entonces abre la interfaz.

## Detener y reiniciar

```powershell
Stop-ScheduledTask -TaskName 'Doble3D-n8n'
Start-ScheduledTask -TaskName 'Doble3D-n8n'
```

Para mantenimiento, deshabilita primero la tarea y luego detén la ejecución desde n8n. Evita terminar procesos Node genéricos: podrían pertenecer a otras aplicaciones.

```powershell
Disable-ScheduledTask -TaskName 'Doble3D-n8n'
Stop-ScheduledTask -TaskName 'Doble3D-n8n'
```

## Logs

El runner agrega salida a `logs/n8n.log`. Al superar 10 MB rota el archivo con fecha y hora. Para inspeccionar las últimas líneas:

```powershell
Get-Content .\logs\n8n.log -Tail 100
```

No copies valores de autenticación, cuerpos completos de solicitudes ni datos sensibles a tickets o correos.

## Prueba manual segura

1. Confirma que el export maestro está inactivo y que `STATUS` es `draft`.
2. Importa primero el manejador de errores, configura SMTP y actualiza su ID en el maestro.
3. Importa el maestro sin activarlo.
4. Ejecuta el trigger manual con un tema permitido.
5. Verifica que se cree como máximo un borrador, con fuente HTTPS, enlace comercial, metadatos válidos e imagen.
6. Repite la ejecución y confirma que se detiene por tema o slug duplicado antes de subir medios.
7. Elimina sólo los artefactos de prueba identificados; no borres contenido existente.

## Desactivar schedules

En n8n, desactiva el workflow maestro con el interruptor `Active`. Para impedir que la instancia arranque al iniciar sesión:

```powershell
Disable-ScheduledTask -TaskName 'Doble3D-n8n'
```

## Restaurar un export

1. Desactiva el workflow afectado.
2. Exporta su estado actual para conservar evidencia.
3. Importa la copia validada desde `audit/backups/<timestamp>/task-4/`.
4. Mantén `active=false` y revisa conexiones, credenciales e ID del manejador de errores.
5. Ejecuta las pruebas locales y una prueba manual antes de considerar la reactivación.

## Volver todas las publicaciones automatizadas a draft

El control primario es `const STATUS = 'draft'` en `Ensamblar post`. Si un piloto publicó contenido, identifica únicamente los posts creados por sus slugs o registros de ejecución y cambia cada uno a `draft` desde WordPress. No hagas una actualización masiva sin una lista revisada y un respaldo SQL validado.

## Recuperación

Si n8n no supera salud:

1. Desactiva la tarea para detener el ciclo de reinicio.
2. Revisa `logs/n8n.log` y el historial de la tarea programada.
3. Confirma que el puerto 5678 esté libre y que `n8n.cmd` sea visible para la cuenta operativa.
4. Restaura el último export validado sin activarlo.
5. Si hay riesgo de escrituras incompletas, mantén los schedules desactivados y revisa WordPress antes de reintentar.

