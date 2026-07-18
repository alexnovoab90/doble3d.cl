# Diseño de remediación integral para doble3d.cl

**Fecha:** 18 de julio de 2026  
**Estado:** Diseño aprobado  
**Objetivo:** Corregir los problemas editoriales, SEO, operativos y de automatización detectados en doble3d.cl sin perder tráfico ni publicar contenido no validado.

## Alcance

El trabajo se divide en cuatro frentes coordinados:

1. Automatización n8n confiable y gradual.
2. Limpieza editorial basada en datos.
3. SEO técnico y saneamiento de WordPress.
4. Publicación y medición de contenidos pilar.

Las credenciales y su rotación quedan fuera de alcance por decisión del propietario. No se imprimirán, trasladarán ni modificarán durante esta iniciativa.

## Estado inicial confirmado

- WordPress publica 57 entradas.
- Ninguno de los tres artículos pilar documentados está publicado con el título y enfoque definidos.
- Diez títulos históricos contienen HTML incrustado.
- Existen publicaciones alejadas del nicho comercial principal.
- Las cuatro páginas de servicio carecen de meta-description.
- El Home y el Blog tienen títulos más largos de lo recomendable.
- n8n contiene tres variantes de un flujo que pueden solaparse si se activan juntas.
- Los flujos exportados publican con estado `publish`, sin revisión humana, control de duplicados ni rama formal de error.
- El arranque local de n8n depende de una ventana abierta y no constituye un servicio persistente.
- Un HTML público residual duplica una entrada existente de WordPress.
- La respuesta HTTP, caché, HTTPS, sitemap, canonical, datos estructurados y códigos 404 presentan una base saludable.

## Principios de ejecución

- Respaldar antes de modificar producción.
- Trabajar en staging o copia local cuando el cambio pueda afectar renderizado, publicación o redirecciones.
- No retirar URLs por intuición: usar datos de tráfico, impresiones, enlaces y relevancia.
- Toda URL retirada debe conservar su valor mediante redirección 301 hacia el destino semánticamente más cercano.
- Mantener un único flujo maestro de publicación.
- Publicar inicialmente como borrador y exigir revisión humana durante dos semanas.
- Rechazar automáticamente contenido incompleto, duplicado, fuera de nicho o sin fuentes suficientes.
- Mantener reversión documentada para cada cambio de producción.

## Arquitectura propuesta

### Frente A: automatización n8n

Se conservará un flujo maestro con dos disparadores de calendario, martes y jueves a las 11:00 en `America/Santiago`. Las variantes redundantes quedarán archivadas y desactivadas.

El flujo seguirá esta secuencia:

1. Seleccionar un tema dentro de una taxonomía autorizada.
2. Consultar WordPress para detectar coincidencias por slug, título, keyword y similitud temática.
3. Recopilar fuentes externas y conservar URL, título, fecha y medio.
4. Generar una respuesta estructurada con título, slug, extracto, contenido, metadatos, FAQ, categoría, etiquetas y datos de imagen.
5. Validar estructura, longitud, fuentes, enlaces, categoría, etiquetas, imagen y coherencia con la marca.
6. Crear o reutilizar etiquetas sin duplicarlas.
7. Subir la imagen y validar texto alternativo.
8. Crear la entrada de WordPress como `draft`.
9. Registrar la ejecución y notificar el resultado.

Durante el piloto de dos semanas la publicación será manual. La publicación automática solo podrá habilitarse después de que todas las ejecuciones del piloto cumplan los criterios de aceptación.

### Controles del flujo

- Lista positiva de temas y keywords.
- Bloqueo de temas de consumo general sin relación directa con VR, XR, animación 3D, capacitación, minería o industria.
- Detección de duplicados antes de redactar y antes de crear el borrador.
- Metatítulos y metadescripciones generados dentro del límite, no recortados mecánicamente.
- Prohibición de afirmaciones cuantitativas sin fuente identificable.
- Validación de enlaces internos contra las URLs comerciales actuales.
- Máximo de una categoría principal y cinco etiquetas normalizadas.
- Reintentos limitados para servicios externos y WordPress.
- Rama de error con registro de etapa, causa y payload no sensible.
- Idempotencia mediante una clave formada por fecha de ejecución, tema y slug.

### Frente B: limpieza editorial

Cada una de las 57 entradas se clasificará con una matriz que considere relevancia, tráfico orgánico, impresiones, backlinks, calidad, actualidad y canibalización.

Las acciones posibles son:

- **Conservar:** contenido relevante y correcto.
- **Mejorar:** tema válido con problemas de título, fuentes, estructura, metadatos o enlaces.
- **Fusionar:** dos o más contenidos que compiten por la misma intención.
- **Retirar:** contenido irrelevante, defectuoso o sin valor demostrable.
- **Redirigir:** toda URL retirada se redirige al contenido conservado más cercano.

No se aplicarán eliminaciones masivas. Las redirecciones se probarán individualmente y el sitemap se regenerará después de la limpieza.

### Frente C: SEO técnico y WordPress

Se corregirán las meta-descriptions de Realidad Virtual, Animación 3D, CORE y Gamificación SCORM. También se ajustarán los títulos de Home y Blog, y se corregirán los títulos históricos con HTML.

La documentación y el enlazado interno pasarán de anclas antiguas como `/#servicios` y `/#core` a las páginas de servicio canónicas. Se revisarán canonical, robots, sitemap, schema, breadcrumbs, H1, alt de imágenes y enlaces rotos después de cada lote.

El HTML residual duplicado se sustituirá por una redirección 301 hacia la entrada canónica. Los archivos públicos innecesarios se retirarán o bloquearán, conservando únicamente los endpoints que tengan un consumidor confirmado. Los endpoints PHP personalizados se revisarán por método HTTP, autenticación, validación de entrada, límites de uso y manejo de errores, sin cambiar credenciales en esta fase.

### Frente D: contenidos pilar

Se prepararán tres artículos:

1. Capacitación en realidad virtual para minería.
2. Animación 3D de procesos mineros e industriales.
3. CORE como plataforma de gestión de entrenamiento VR.

Cada artículo debe incluir:

- Keyword principal e intención de búsqueda definida.
- Fuentes primarias o reputadas para toda métrica cuantitativa.
- Meta-title y meta-description completos.
- Un solo H1 y estructura H2/H3 coherente.
- Enlaces hacia las páginas comerciales actuales.
- CTA relacionado con la intención del artículo.
- FAQ y schema válido.
- Imagen optimizada con alt descriptivo.
- Autor, fecha de publicación y fecha de revisión.
- Revisión humana antes de publicar.

Los artículos automáticos posteriores funcionarán como contenido de apoyo y enlazarán a estos pilares sin competir por la misma keyword.

## Manejo de errores y reversión

- Todo cambio en WordPress tendrá respaldo y lista de URLs afectadas.
- Las redirecciones se almacenarán en un inventario versionado.
- Un fallo en investigación, redacción, imagen o WordPress detendrá el flujo antes de crear una entrada pública.
- Los reintentos no podrán crear entradas, medios ni etiquetas duplicados.
- Si el piloto produce un borrador defectuoso, se corrige la regla que permitió el error antes de continuar.
- Si la publicación automática falla posteriormente, el flujo volverá a modo `draft` hasta completar una nueva validación.

## Estrategia de pruebas

### Automatización

- Ejecutar cada rama con datos de prueba controlados.
- Comprobar rechazo de temas fuera de nicho.
- Comprobar detección de slug y título duplicados.
- Simular fallos de fuente, IA, imagen y WordPress.
- Ejecutar dos veces el mismo evento y confirmar idempotencia.
- Verificar que el resultado del piloto siempre sea `draft`.

### WordPress y SEO

- Validar HTTP 200 en páginas conservadas y 301 en retiradas.
- Validar que ninguna redirección forme cadenas o bucles.
- Confirmar un canonical por página.
- Confirmar meta-description en páginas comerciales.
- Confirmar que sitemap y schema sean válidos.
- Comprobar títulos, snippets, enlaces e imágenes en escritorio y móvil.
- Revisar consola del navegador y registros de error después del despliegue.

### Editorial

- Aplicar una lista de comprobación a cada borrador.
- Verificar manualmente fuentes y afirmaciones cuantitativas.
- Confirmar que la keyword no canibalice contenido existente.
- Confirmar coherencia de tono, CTA y páginas enlazadas.

## Despliegue gradual

### Etapa 1: preparación

- Respaldos, inventario y línea base.
- Congelar publicación automática.
- Preparar staging y registro de redirecciones.

### Etapa 2: correcciones

- Consolidar n8n.
- Corregir SEO técnico y títulos defectuosos.
- Clasificar y limpiar contenidos.
- Publicar las piezas pilar como borradores revisados.

### Etapa 3: piloto de dos semanas

- Dos borradores semanales generados por n8n.
- Revisión humana de todos los borradores.
- Registro de defectos, tiempos y correcciones.

### Etapa 4: activación condicionada

La publicación automática se habilitará solo si el piloto termina sin duplicados, contenido fuera de nicho, metadatos incompletos, enlaces rotos, imágenes sin alt, errores no gestionados o afirmaciones cuantitativas sin fuente.

## Criterios de aceptación globales

- Existe un único flujo maestro activo.
- Durante el piloto, todas las entradas se crean como borrador.
- No se publican duplicados ni temas fuera de nicho.
- Las cuatro páginas comerciales tienen meta-description válida.
- Home y Blog tienen títulos completos y razonables.
- Los diez títulos históricos con HTML están corregidos.
- El HTML duplicado responde con redirección 301 a su entrada canónica.
- Cada URL retirada tiene decisión documentada y redirección probada.
- Los tres artículos pilar están publicados y enlazan a las páginas comerciales actuales.
- Todas las métricas comerciales publicadas tienen fuente verificable.
- Sitemap, canonical, schema, enlaces e imágenes superan la verificación posterior al despliegue.
- El periodo gradual de dos semanas queda documentado con resultados y decisión final.

## Fuera de alcance

- Rotación, sustitución o traslado de credenciales.
- Rediseño visual completo del sitio.
- Migración de hosting o cambio de CMS.
- Desarrollo de nuevas funciones de CORE.
- Campañas pagadas o gestión cotidiana de redes sociales.

