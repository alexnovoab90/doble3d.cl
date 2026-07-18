# Checklist del piloto editorial

Este checklist se completa para cada uno de los cuatro borradores programados. Un defecto crítico detiene el piloto, mantiene el workflow en `draft` y reinicia la ventana de cuatro borradores después de corregir la regla responsable.

## Antes de generar

- [ ] El workflow maestro está importado pero publica sólo como `draft`.
- [ ] Los exports históricos permanecen inactivos.
- [ ] El manejador de errores está configurado y probado sin exponer datos sensibles.
- [ ] Existe respaldo SQL validado y manifiesto de rollback completo.
- [ ] La captura pública pasa para metadatos y títulos saneados.

## Contenido del borrador

- [ ] Tema relevante para realidad virtual, XR, animación 3D, CORE, SCORM, capacitación o seguridad industrial/minera.
- [ ] Búsqueda de tema y slug no detecta duplicados.
- [ ] Meta title entre 35 y 60 caracteres.
- [ ] Meta description entre 120 y 155 caracteres.
- [ ] Todas las afirmaciones verificables tienen fuente HTTPS real; no hay cifras sin respaldo.
- [ ] Incluye al menos un enlace comercial canónico de Doble 3D.
- [ ] Incluye al menos tres preguntas frecuentes útiles y autocontenidas.
- [ ] No hay enlaces rotos, redirecciones innecesarias ni URLs inventadas.
- [ ] Imagen destacada válida, dimensiones revisadas y alt descriptivo.
- [ ] Una categoría y máximo cinco tags normalizados.

## Preview y publicación manual

- [ ] Preview de escritorio revisado.
- [ ] Preview móvil revisado.
- [ ] H1 único y jerarquía H2/H3 lógica.
- [ ] Canonical correcto.
- [ ] Schema Article y FAQ válido.
- [ ] Autor y fecha correctos.
- [ ] Sin errores de consola, PHP ni ejecución n8n.
- [ ] Aprobación del revisor registrada antes de publicar manualmente.

## Después de publicar

- [ ] URL responde 200.
- [ ] Título y descripción renderizados coinciden con el borrador aprobado.
- [ ] Canonical, schema, enlaces e imagen alt siguen correctos.
- [ ] URL aparece en sitemap cuando corresponde.
- [ ] No se creó un segundo post o medio para la misma ejecución.
- [ ] Resultado y minutos de revisión registrados en `pilot-log.csv`.

