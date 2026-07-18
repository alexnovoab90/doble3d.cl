# Doble3D Remediation Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Corregir la automatización, el SEO técnico, la deuda editorial y los residuos públicos de doble3d.cl, desplegando primero en modo borrador y habilitando publicación automática solo después de un piloto exitoso de dos semanas.

**Architecture:** El trabajo se ejecuta en cuatro líneas coordinadas: controles locales verificables para n8n, cambios acotados en WordPress/FTP, saneamiento editorial basado en datos y un piloto gradual. Un único flujo maestro genera borradores, aplica controles antes de escribir en WordPress y conserva WordPress como fuente de verdad para evitar duplicados.

**Tech Stack:** WordPress REST API, Yoast SEO, PHP, Apache/LiteSpeed `.htaccess`, n8n, JavaScript de nodos Code, PowerShell, Node.js `node:test`, FTPS explícito.

## Global Constraints

- Las credenciales y su rotación quedan fuera de alcance; no imprimirlas, moverlas ni modificarlas.
- Respaldar archivos y base de datos antes de cualquier cambio remoto.
- No borrar entradas por intuición: conservar si no existen datos suficientes.
- Toda URL retirada debe responder 301 hacia el destino semánticamente más cercano.
- Mantener un único flujo maestro; las variantes redundantes quedan archivadas y desactivadas.
- Durante dos semanas, n8n debe crear exclusivamente borradores.
- La automatización se detiene ante duplicados, temas fuera de nicho, fuentes insuficientes o metadatos inválidos.
- El workspace no es un repositorio Git. Cada tarea termina con un snapshot fechado; no inicializar Git ni versionar los JSON con secretos durante este alcance.
- Las acciones remotas de escritura requieren revisión del diff o payload y aprobación inmediatamente antes de ejecutarse.

## Workstream and Schedule

| Jornada | Entregable principal |
|---|---|
| Día 1 | Respaldo, inventario y línea base reproducible |
| Día 2 | Política editorial y pruebas automatizadas de n8n |
| Días 3–4 | Flujo maestro en borrador, duplicados, validaciones y errores |
| Día 5 | Ejecución persistente y runbook de n8n |
| Día 6 | Metadatos y SEO de páginas comerciales |
| Día 7 | Inventario editorial y corrección de títulos defectuosos |
| Día 8 | Redirección del HTML duplicado y saneamiento de archivos públicos |
| Días 9–10 | Tres artículos pilar, QA y despliegue como borradores |
| Semanas 3–4 | Piloto de cuatro borradores y decisión de automatización |

---

### Task 1: Capture a Reproducible Baseline and Backups

**Files:**
- Create: `audit/scripts/capture-public-baseline.ps1`
- Create: `audit/scripts/export-editorial-inventory.ps1`
- Create: `audit/baseline/.gitkeep`
- Create: `audit/backups/.gitkeep`
- Create: `audit/README.md`
- Read remotely: `FTP:/public_html/.htaccess`
- Read remotely: `FTP:/public_html/wp-content/themes/doble3d/`

**Interfaces:**
- Consumes: Sitio público y API pública `https://doble3d.cl/wp-json/wp/v2/posts`.
- Produces: `audit/baseline/site-YYYYMMDD-HHMMSS.json`, `audit/baseline/posts-YYYYMMDD-HHMMSS.csv` y respaldo remoto descargado fuera de `public_html`.

- [ ] **Step 1: Create the public baseline script**

```powershell
param([string]$OutputDir = "$PSScriptRoot\..\baseline")
$ErrorActionPreference = 'Stop'
New-Item -ItemType Directory -Force $OutputDir | Out-Null
$stamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$urls = @(
  'https://doble3d.cl/',
  'https://doble3d.cl/blog/',
  'https://doble3d.cl/servicios/realidad-virtual/',
  'https://doble3d.cl/servicios/animacion-3d/',
  'https://doble3d.cl/servicios/core/',
  'https://doble3d.cl/servicios/gamificacion-scorm/',
  'https://doble3d.cl/sitemap_index.xml'
)
$rows = foreach ($url in $urls) {
  $html = (curl.exe --silent --show-error --fail $url) -join "`n"
  $status = curl.exe --silent --show-error --output NUL --write-out '%{http_code}' $url
  $title = [regex]::Match($html, '<title[^>]*>(.*?)</title>', 'IgnoreCase,Singleline').Groups[1].Value
  $desc = [regex]::Match($html, '<meta\s+[^>]*name=["'']description["''][^>]*content=["'']([^"'']*)', 'IgnoreCase').Groups[1].Value
  [pscustomobject]@{ url=$url; status=[int]$status; title=$title; titleLength=$title.Length; description=$desc; descriptionLength=$desc.Length }
}
$rows | ConvertTo-Json -Depth 4 | Set-Content -Encoding utf8 "$OutputDir\site-$stamp.json"
```

- [ ] **Step 2: Run the baseline script and verify current failures are captured**

Run: `powershell -ExecutionPolicy Bypass -File audit/scripts/capture-public-baseline.ps1`

Expected: exit 0; the four service URLs report `descriptionLength: 0`.

- [ ] **Step 3: Create the editorial inventory script**

```powershell
param([string]$OutputDir = "$PSScriptRoot\..\baseline")
$ErrorActionPreference = 'Stop'
New-Item -ItemType Directory -Force $OutputDir | Out-Null
$stamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$api = 'https://doble3d.cl/wp-json/wp/v2/posts?per_page=100&_fields=id,date,modified,slug,link,title,content,excerpt,yoast_head_json'
$posts = curl.exe --silent --show-error --fail $api | ConvertFrom-Json
$rows = foreach ($post in $posts) {
  $title = [string]$post.title.rendered
  $content = [string]$post.content.rendered
  [pscustomobject]@{
    id=$post.id; date=$post.date; modified=$post.modified; slug=$post.slug; link=$post.link
    title=$title; malformedTitle=($title -match '^\s*<')
    words=(($content -replace '<[^>]+>',' ' -replace '\s+',' ').Trim() -split '\s+').Count
    internalLinks=([regex]::Matches($content,'href=["''](?:https://doble3d\.cl/|/)')).Count
    externalSources=([regex]::Matches($content,'href=["'']https?://(?!doble3d\.cl|wa\.me)')).Count
    metaTitle=[string]$post.yoast_head_json.title
    metaDescription=[string]$post.yoast_head_json.description
  }
}
$rows | Export-Csv -NoTypeInformation -Encoding utf8 "$OutputDir\posts-$stamp.csv"
```

- [ ] **Step 4: Run inventory and verify scope**

Run: `powershell -ExecutionPolicy Bypass -File audit/scripts/export-editorial-inventory.ps1`

Expected: 57 rows and 10 rows with `malformedTitle=True` at the initial baseline.

- [ ] **Step 5: Download remote backups before writes**

Download these exact paths to `audit/backups/<timestamp>/`: `/public_html/.htaccess`, `/public_html/wp-content/themes/doble3d/`, `/public_html/image-gen.php`, `/public_html/media-gen.php`, and a full WordPress database export from cPanel.

Expected: every downloaded file has a non-zero size; database archive opens and contains WordPress tables.

- [ ] **Step 6: Write the rollback manifest**

Create `audit/backups/<timestamp>/MANIFEST.md` listing the source path, local backup path, byte size, timestamp and restoration command for every artifact.

Expected: no remote write is allowed until the manifest is complete.

---

### Task 2: Add a Testable Editorial Policy for n8n

**Files:**
- Create: `n8n/workflow-policy.json`
- Create: `n8n/lib/policy.mjs`
- Create: `n8n/tests/policy.test.mjs`
- Create: `n8n/tests/workflow-contract.test.mjs`
- Test: `n8n/auto-post-doble3d.json`

**Interfaces:**
- Consumes: Artículo estructurado `{titulo, slug, meta_title, meta_description, focus_keyword, categoria, tags, fuentes, faq, content}` y posts existentes normalizados como `{slug, title}`.
- Produces: `validateTopic(topic, policy)`, `validateArticle(article, existingPosts, policy)` and `idempotencyKey(date, topic, slug)`.

- [ ] **Step 1: Create the policy file**

```json
{
  "pilotStatus": "draft",
  "timezone": "America/Santiago",
  "allowedTerms": ["realidad virtual", "vr", "xr", "realidad mixta", "animación 3d", "scorm", "core", "capacitación", "minería", "industrial", "seguridad", "gemelo digital"],
  "blockedTerms": ["iphone", "ipad", "macbook", "switch 2", "cirugía", "gaming de consumo", "precios de celulares"],
  "commercialUrls": [
    "https://doble3d.cl/servicios/realidad-virtual/",
    "https://doble3d.cl/servicios/animacion-3d/",
    "https://doble3d.cl/servicios/core/",
    "https://doble3d.cl/servicios/gamificacion-scorm/"
  ],
  "limits": {"titleMin": 35, "titleMax": 60, "descriptionMin": 120, "descriptionMax": 155, "tagsMax": 5, "faqMin": 3, "sourcesMin": 1, "duplicateSimilarity": 0.7}
}
```

- [ ] **Step 2: Write failing policy tests**

```javascript
import test from 'node:test';
import assert from 'node:assert/strict';
import policy from '../workflow-policy.json' with { type: 'json' };
import { validateTopic, validateArticle, idempotencyKey } from '../lib/policy.mjs';

test('rejects consumer technology topics', () => {
  assert.equal(validateTopic('Precios de iPhone y MacBook 2026', policy).ok, false);
});

test('accepts industrial VR topics', () => {
  assert.equal(validateTopic('Realidad virtual para capacitación minera', policy).ok, true);
});

test('rejects an article without sources and commercial links', () => {
  const article = {titulo:'Realidad virtual para capacitación minera en Chile',slug:'realidad-virtual-capacitacion-minera',meta_title:'Realidad virtual para capacitación minera en Chile',meta_description:'La realidad virtual permite entrenar procedimientos críticos de minería en entornos seguros, medibles y sin detener equipos productivos.',focus_keyword:'realidad virtual minería chile',categoria:'realidad virtual',tags:['VR'],fuentes:[],faq:[{},{},{}],content:'<p>Contenido sin enlace comercial.</p>'};
  assert.equal(validateArticle(article, [], policy).ok, false);
});

test('builds a stable idempotency key', () => {
  assert.equal(idempotencyKey('2026-07-21','VR minería','realidad-virtual-mineria'),'2026-07-21|vr-mineria|realidad-virtual-mineria');
});
```

- [ ] **Step 3: Run tests to verify they fail**

Run: `node --test n8n/tests/policy.test.mjs`

Expected: FAIL because `n8n/lib/policy.mjs` does not exist.

- [ ] **Step 4: Implement the policy functions**

```javascript
const normalize = value => String(value ?? '').normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');
const tokens = value => new Set(normalize(value).split('-').filter(Boolean));
const similarity = (a,b) => { const x=tokens(a), y=tokens(b); const both=[...x].filter(v=>y.has(v)).length; const all=new Set([...x,...y]).size; return all ? both/all : 0; };

export function validateTopic(topic, policy) {
  const text = String(topic ?? '').toLowerCase();
  if (policy.blockedTerms.some(term => text.includes(term))) return {ok:false,errors:['blocked_topic']};
  if (!policy.allowedTerms.some(term => text.includes(term))) return {ok:false,errors:['outside_niche']};
  return {ok:true,errors:[]};
}

export function idempotencyKey(date, topic, slug) {
  return `${date}|${normalize(topic)}|${normalize(slug)}`;
}

export function validateArticle(article, existingPosts, policy) {
  const errors=[];
  const required=['titulo','slug','meta_title','meta_description','focus_keyword','categoria','content'];
  for (const key of required) if (!String(article[key] ?? '').trim()) errors.push(`missing_${key}`);
  if ((article.meta_title ?? '').length < policy.limits.titleMin || (article.meta_title ?? '').length > policy.limits.titleMax) errors.push('invalid_meta_title_length');
  if ((article.meta_description ?? '').length < policy.limits.descriptionMin || (article.meta_description ?? '').length > policy.limits.descriptionMax) errors.push('invalid_meta_description_length');
  if ((article.tags ?? []).length > policy.limits.tagsMax) errors.push('too_many_tags');
  if ((article.faq ?? []).length < policy.limits.faqMin) errors.push('insufficient_faq');
  if ((article.fuentes ?? []).filter(x=>/^https:\/\//.test(x.url ?? x)).length < policy.limits.sourcesMin) errors.push('insufficient_sources');
  if (!policy.commercialUrls.some(url => String(article.content ?? '').includes(url))) errors.push('missing_commercial_link');
  if (existingPosts.some(p => normalize(p.slug) === normalize(article.slug) || similarity(p.title, article.titulo) >= policy.limits.duplicateSimilarity)) errors.push('duplicate_article');
  return {ok:errors.length===0,errors};
}
```

- [ ] **Step 5: Run policy tests**

Run: `node --test n8n/tests/policy.test.mjs`

Expected: 4 tests pass, 0 fail.

- [ ] **Step 6: Write the workflow contract test**

```javascript
import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync, readdirSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const workflow = JSON.parse(readFileSync(join(root, 'auto-post-doble3d.json'), 'utf8'));
const node = name => workflow.nodes.find(item => item.name === name);

test('uses the Santiago Tuesday/Thursday schedule', () => {
  assert.equal(workflow.settings.timezone, 'America/Santiago');
  const schedules = workflow.nodes.filter(item => item.type === 'n8n-nodes-base.scheduleTrigger');
  assert.equal(schedules.length, 1);
  assert.equal(schedules[0].parameters.rule.interval[0].expression, '0 11 * * 2,4');
});

test('keeps the pilot in draft mode', () => {
  assert.match(node('Ensamblar post').parameters.jsCode, /const STATUS = 'draft'/);
});

test('contains duplicate and article gates', () => {
  assert.ok(node('Buscar duplicado'));
  assert.ok(node('Validar candidato'));
  assert.ok(node('Validar artículo'));
  assert.ok(node('Buscar slug final'));
});

test('keeps only the master export in the n8n root', () => {
  const exports = readdirSync(root).filter(name => /^auto-post.*\.json$/.test(name));
  assert.deepEqual(exports, ['auto-post-doble3d.json']);
});
```

- [ ] **Step 7: Run the contract test and record the expected red state**

Run: `node --test n8n/tests/workflow-contract.test.mjs`

Expected: FAIL on `status=publish`, missing validation nodes and redundant JSON files.

Snapshot: copy the new policy and tests to `audit/backups/<timestamp>/task-2/`.

---

### Task 3: Consolidate n8n into One Draft-Only Master Workflow

**Files:**
- Modify: `n8n/auto-post-doble3d.json`
- Create: `n8n/archive/auto-post-doble3d-martes.json`
- Create: `n8n/archive/auto-post-doble3d-jueves.json`
- Modify: `n8n/README.md`
- Test: `n8n/tests/workflow-contract.test.mjs`

**Interfaces:**
- Consumes: `workflow-policy.json` rules copied into n8n Code nodes.
- Produces: One export with `Schedule → Elegir tema → Buscar duplicado → Validar candidato → Noticias → Preparar prompt → DeepSeek → Validar artículo → Ensamblar → Imagen → Media → Alt → Publicar borrador`.

- [ ] **Step 1: Move the split exports to the archive directory**

Move, do not delete, the Tuesday and Thursday exports into `n8n/archive/`. Keep `auto-post-doble3d.json` as the only workflow export in `n8n/` root.

- [ ] **Step 2: Change the master status to draft**

In node `Ensamblar post`, replace exactly:

```javascript
const STATUS = 'publish';
```

with:

```javascript
const STATUS = 'draft';
```

- [ ] **Step 3: Add the WordPress duplicate preflight**

Add HTTP Request node `Buscar duplicado` before research:

```text
GET https://doble3d.cl/wp-json/wp/v2/posts
Query: search={{ $json.topic }}, per_page=20, status=publish,draft,pending,private
Authentication: reuse the existing WordPress authentication configuration without changing it
```

Add Code node `Validar candidato` that calculates token similarity and throws `DUPLICATE_TOPIC` when any returned title reaches `0.70`; it throws `OUTSIDE_NICHE` for blocked or non-allowed terms.

- [ ] **Step 4: Make DeepSeek return verifiable structured data**

Require these exact top-level properties: `titulo`, `slug`, `meta_title`, `meta_description`, `focus_keyword`, `categoria`, `tags`, `fuentes`, `faq`, `excerpt`, `content`, `titulo_imagen`, `subtitulo_imagen`, `tag_top`, `tag_1`, `tag_2`.

Require `fuentes` entries shaped as:

```json
{"titulo":"Nombre de la fuente","url":"https://example.org/documento","fecha":"2026-07-01","medio":"Organización responsable"}
```

- [ ] **Step 5: Replace mechanical metadata truncation**

Remove the `trunc(..., 60)` and `trunc(..., 155)` behavior. Reject over-limit metadata in `Validar artículo` and send the payload back through one correction request to DeepSeek. If the corrected payload still fails, stop the execution.

- [ ] **Step 6: Add the article validator**

`Validar artículo` must reject missing fields, title outside 35–60 characters, description outside 120–155 characters, fewer than three FAQ items, more than five tags, no HTTPS source, no commercial link, invalid slug or duplicated title/slug.

- [ ] **Step 7: Export the edited workflow and run contract tests**

Run: `node --test n8n/tests/policy.test.mjs n8n/tests/workflow-contract.test.mjs`

Expected: all tests pass; the export remains inactive until Task 5.

Snapshot: `audit/backups/<timestamp>/task-3/auto-post-doble3d.json`.

---

### Task 4: Add Idempotency, Retries and an Error Workflow

**Files:**
- Modify: `n8n/auto-post-doble3d.json`
- Create: `n8n/error-handler-doble3d.json`
- Modify: `n8n/tests/workflow-contract.test.mjs`
- Create: `n8n/tests/fixtures/duplicate-posts.json`
- Create: `n8n/tests/fixtures/valid-article.json`

**Interfaces:**
- Consumes: Slug, topic and execution date.
- Produces: At most one WordPress draft for each idempotency key and a recorded error notification for every terminal failure.

- [ ] **Step 1: Add the final slug preflight**

Immediately before uploading media, query:

```text
GET /wp-json/wp/v2/posts?slug={{ $json.slug }}&status=publish,draft,pending,private&context=edit
```

If any item exists, stop with `DUPLICATE_SLUG` before generating or uploading another image.

- [ ] **Step 2: Configure bounded retries**

Set HTTP retry to three attempts with 5, 15 and 30 second delays for Serper, DeepSeek, image retrieval and WordPress. Do not retry HTTP 400, 401 or 403. Retry 429 and 5xx.

- [ ] **Step 3: Create the error workflow**

Use `Error Trigger → Formatear error → Send Email`. Send to `dwolfft@doble3d.cl` with subject:

```text
[doble3d.cl] Falló Auto Post — {{ $json.workflow.name }} — {{ $json.execution.id }}
```

The body includes workflow, execution ID, node, timestamp, error type and execution URL. It must not include headers, authorization values or complete request bodies.

- [ ] **Step 4: Attach the error workflow to the master workflow**

Set the workflow setting `errorWorkflow` to the imported error-handler workflow ID.

- [ ] **Step 5: Test duplicate and failure paths manually**

Run once with fixture `duplicate-posts.json`: expected `DUPLICATE_TOPIC`, zero media and zero drafts. Run twice with `valid-article.json`: first run creates one draft; second run stops at `DUPLICATE_SLUG`.

- [ ] **Step 6: Extend and run the contract tests**

Expected assertions: retry settings exist, `Buscar slug final` exists, error workflow exists, and WordPress status remains `draft`.

Snapshot both workflow exports in `audit/backups/<timestamp>/task-4/`.

---

### Task 5: Make n8n Persistent and Document Operations

**Files:**
- Create: `n8n/scripts/start-n8n.ps1`
- Create: `n8n/scripts/healthcheck.ps1`
- Create: `n8n/scripts/register-n8n-task.ps1`
- Modify: `n8n/iniciar-n8n.bat`
- Create: `n8n/RUNBOOK.md`

**Interfaces:**
- Consumes: Existing local n8n installation and unchanged n8n data directory.
- Produces: Scheduled startup at user logon, restart on failure, health check and recovery instructions.

- [ ] **Step 1: Create the runner**

```powershell
$ErrorActionPreference='Stop'
$env:PATH="C:\nvm4w\nodejs;$env:APPDATA\npm;$env:PATH"
$logDir=Join-Path $PSScriptRoot '..\logs'
New-Item -ItemType Directory -Force $logDir | Out-Null
& n8n start *>> (Join-Path $logDir 'n8n.log')
exit $LASTEXITCODE
```

- [ ] **Step 2: Create the health check**

```powershell
$response=Invoke-WebRequest -UseBasicParsing -Uri 'http://localhost:5678/healthz' -TimeoutSec 5
if($response.StatusCode -ne 200){ throw "n8n health check failed: $($response.StatusCode)" }
Write-Output 'n8n healthy'
```

- [ ] **Step 3: Register a scheduled task with restart settings**

```powershell
$ErrorActionPreference='Stop'
$runner=(Resolve-Path (Join-Path $PSScriptRoot 'start-n8n.ps1')).Path
$action=New-ScheduledTaskAction -Execute 'powershell.exe' -Argument "-NoProfile -ExecutionPolicy Bypass -File `"$runner`""
$trigger=New-ScheduledTaskTrigger -AtLogOn -User "$env:USERDOMAIN\$env:USERNAME"
$settings=New-ScheduledTaskSettingsSet -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -RestartCount 3 -RestartInterval (New-TimeSpan -Minutes 1) -ExecutionTimeLimit (New-TimeSpan -Days 3650)
$principal=New-ScheduledTaskPrincipal -UserId "$env:USERDOMAIN\$env:USERNAME" -LogonType Interactive -RunLevel Limited
Register-ScheduledTask -TaskName 'Doble3D-n8n' -Action $action -Trigger $trigger -Settings $settings -Principal $principal -Force
```

- [ ] **Step 4: Replace the launcher behavior**

`iniciar-n8n.bat` should run `healthcheck.ps1`; if healthy, open `http://localhost:5678`; if unhealthy, start scheduled task `Doble3D-n8n` and retry health once after 15 seconds.

- [ ] **Step 5: Test with explicit approval for the system change**

Run registration, log off/on or trigger the task manually, close all launch windows and run health check.

Expected: `n8n healthy`; the process remains available without an open terminal.

- [ ] **Step 6: Complete the runbook**

Document start, stop, health, logs, manual workflow test, disabling schedules, restoring an export and returning all posts to `draft` mode.

---

### Task 6: Correct Commercial Metadata and Navigation Targets

**Files:**
- Create: `wordpress/seo/metadata-map.csv`
- Modify remotely through WordPress/Yoast: Home, Blog and four service pages
- Modify: `2026-07-17-18-06-34/SEO_Metadata_Linking_Strategy.md`
- Test: `audit/scripts/capture-public-baseline.ps1`

**Interfaces:**
- Consumes: Canonical public URLs.
- Produces: Complete titles/descriptions and documentation that links to canonical service pages.

- [ ] **Step 1: Create the exact metadata map**

```csv
url,title,description
https://doble3d.cl/,Doble 3D | Realidad Virtual y Animación 3D Industrial,Soluciones de realidad virtual y animación 3D para minería e industria en Chile. Entrena procedimientos críticos sin detener la faena.
https://doble3d.cl/blog/,Blog de Realidad Virtual y Animación 3D Industrial,Guías y casos sobre realidad virtual, XR, animación 3D y capacitación industrial para minería y operaciones de alto riesgo en Chile.
https://doble3d.cl/servicios/realidad-virtual/,Realidad Virtual Industrial para Minería | Doble 3D,Entrena procedimientos críticos con simuladores de realidad virtual para minería e industria en Chile, sin detener la faena ni exponer a tu equipo.
https://doble3d.cl/servicios/animacion-3d/,Animación 3D Industrial para Minería | Doble 3D,Transforma planos y manuales complejos en animaciones 3D industriales para minería. Explica procesos, mantenimiento e inducciones con claridad.
https://doble3d.cl/servicios/core/,CORE | Gestión de Entrenamiento VR Industrial,Centraliza simulaciones VR, resultados y cohortes con CORE. Integración SCORM para Moodle, SAP SuccessFactors y Cornerstone.
https://doble3d.cl/servicios/gamificacion-scorm/,Gamificación SCORM para Capacitación Industrial,Crea capacitación industrial gamificada con SCORM, métricas y escenarios interactivos, integrada a Moodle, SAP SuccessFactors y Cornerstone.
```

- [ ] **Step 2: Apply metadata through WordPress/Yoast**

Edit only the SEO title and meta-description fields for the six URLs. Do not change slugs or canonical URLs.

- [ ] **Step 3: Update documentation links**

Replace `https://doble3d.cl/#servicios` and `https://doble3d.cl/#core` with the exact matching canonical service URLs.

- [ ] **Step 4: Re-run the public baseline**

Expected: every URL returns 200, every description is 120–155 characters, and Home/Blog titles are complete and at most 60 characters.

Snapshot the before/after baseline files.

---

### Task 7: Build the Editorial Decision Matrix and Fix Malformed Titles

**Files:**
- Create: `audit/editorial/editorial-decision-matrix.csv`
- Create: `audit/editorial/redirect-map.csv`
- Modify remotely: WordPress posts returned with `malformedTitle=True`
- Test: `audit/scripts/export-editorial-inventory.ps1`

**Interfaces:**
- Consumes: Public inventory plus Search Console/Site Kit metrics when available.
- Produces: One deterministic decision per post: `keep`, `improve`, `merge`, `retire`.

- [ ] **Step 1: Create matrix columns**

Use exactly: `id,url,title,organicClicks90d,impressions90d,backlinks,relevance,quality,canibalization,decision,targetUrl,reason,approvedBy,implementedAt`.

- [ ] **Step 2: Import all 57 posts**

Join the public inventory with 90-day Search Console metrics. If metrics are unavailable, leave the post as `keep` or `improve`; never `retire`.

- [ ] **Step 3: Sanitize the ten malformed titles**

For each `malformedTitle=True`, strip HTML tags, decode entities, collapse whitespace and preserve the readable text. Preview every result before updating WordPress.

- [ ] **Step 4: Apply the decision rules**

- `keep`: relevant and technically sound.
- `improve`: relevant but weak title, sources, metadata or linking.
- `merge`: same search intent and weaker than a selected canonical post.
- `retire`: outside niche, zero meaningful metrics, no backlinks and no unique value.

- [ ] **Step 5: Populate redirects before retiring anything**

Every `merge` or `retire` row must have a 200 target URL in `redirect-map.csv`. Reject targets that redirect or do not match the topic.

- [ ] **Step 6: Update only malformed titles in the first batch**

Expected: inventory returns `malformedTitle=False` for all 57 posts; URLs and slugs remain unchanged.

Snapshot the matrix, redirect map and post inventory.

---

### Task 8: Remove Duplicate Public Artifacts and Harden Custom Endpoints

**Files:**
- Modify remotely: `FTP:/public_html/.htaccess`
- Remove after backup: `FTP:/public_html/d3d_post_junio2026.html`
- Review/modify remotely: `FTP:/public_html/image-gen.php`
- Review/retire if unused: `FTP:/public_html/media-gen.php`
- Create: `wordpress/ftp/public-artifact-register.csv`

**Interfaces:**
- Consumes: FTP backup and confirmed consumer inventory.
- Produces: One canonical URL for the duplicate article and explicit disposition for each custom endpoint.

- [ ] **Step 1: Add the exact redirect before the WordPress rewrite block**

```apache
Redirect 301 /d3d_post_junio2026.html https://doble3d.cl/xr-empresarial-pico-ar-ia-junio-2026/
```

- [ ] **Step 2: Verify redirect before deleting the duplicate file**

Run: `curl.exe --head --location --max-redirs 0 https://doble3d.cl/d3d_post_junio2026.html`

Expected: first response is 301 and `Location` is the canonical article.

- [ ] **Step 3: Remove the backed-up duplicate HTML**

Delete only `d3d_post_junio2026.html`; retain the four neutralized PHP helpers returning 410 until the artifact register confirms they have no inbound references.

- [ ] **Step 4: Add safe response controls to image generation**

At the top of `image-gen.php`, after `<?php`, add this guard without altering its existing output design:

```php
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    header('Allow: GET');
    http_response_code(405);
    exit;
}
header('X-Robots-Tag: noindex, nofollow', true);
header('X-Content-Type-Options: nosniff', true);
$limits = ['titulo' => 120, 'subtitulo' => 180, 'tag_top' => 40, 'tag_1' => 40, 'tag_2' => 40];
foreach ($limits as $field => $max) {
    $value = isset($_GET[$field]) ? trim((string) $_GET[$field]) : '';
    if (mb_strlen($value, 'UTF-8') > $max || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', $value)) {
        http_response_code(400);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'invalid input';
        exit;
    }
}
```

- [ ] **Step 5: Decide `media-gen.php` from evidence**

Search current n8n exports, theme files and 30 days of access logs for `media-gen.php`. If no consumer exists, return 410 for seven days, verify no errors, then remove after backup. If a consumer exists, keep it and document the caller; do not change its credential in this scope.

- [ ] **Step 6: Verify public artifacts**

Expected: redirect 301 works, removed duplicate is not indexable, `image-gen.php` rejects over-limit input with 400, and error logs reveal no new PHP warnings.

---

### Task 9: Produce and Publish the Three Pillar Articles as Reviewed Drafts

**Files:**
- Consume: `2026-07-17-18-06-34/SEO_Article_VR_Mining.md`
- Consume: `2026-07-17-18-06-34/SEO_Article_3D_Animation.md`
- Consume: `2026-07-17-18-06-34/SEO_Article_CORE_Platform.md`
- Create: `content/pillars/realidad-virtual-mineria.md`
- Create: `content/pillars/animacion-3d-industrial.md`
- Create: `content/pillars/core-entrenamiento-vr.md`
- Create: `content/pillars/source-register.csv`

**Interfaces:**
- Consumes: Drafts supplied by the documentation and primary/reputable sources.
- Produces: Three WordPress drafts with sources, metadata, internal links, FAQ, schema and featured image.

- [ ] **Step 1: Build the source register before editing claims**

Use columns `article,claim,sourceTitle,sourceUrl,publisher,publishedAt,verifiedAt`. Quantitative claims such as `4×` and `60%` may remain only when a directly supporting source is recorded; otherwise rewrite them without a number.

- [ ] **Step 2: Normalize each local article**

Each file must contain YAML front matter with `title`, `slug`, `metaTitle`, `metaDescription`, `focusKeyword`, `category`, `tags`, `canonical`, `author`, `reviewedAt` and `sources`.

- [ ] **Step 3: Use the canonical commercial links**

- VR article → `/servicios/realidad-virtual/` and `/servicios/core/`.
- 3D article → `/servicios/animacion-3d/`.
- CORE article → `/servicios/core/` and relevant SCORM page.

- [ ] **Step 4: Complete editorial QA**

Verify one H1, logical H2/H3, at least three FAQ items, complete metadata, descriptive alt, CTA, no unsupported metrics, no duplicate intent and no broken links.

- [ ] **Step 5: Create WordPress drafts**

Set status `draft`, assign one category and at most five normalized tags, upload the featured image and set its alt. Do not publish in this task.

- [ ] **Step 6: Validate rendered previews**

Check desktop and mobile preview, schema, headings, links, image dimensions, author/date and Yoast preview. Record reviewer and result in `source-register.csv` or the pilot log.

Snapshot the final Markdown files and WordPress draft IDs.

---

### Task 10: Run Full QA, Two-Week Pilot and Conditional Activation

**Files:**
- Create: `ops/pilot-checklist.md`
- Create: `ops/pilot-log.csv`
- Create: `ops/activation-decision.md`
- Modify after successful pilot only: `n8n/auto-post-doble3d.json`
- Test: all scripts under `audit/scripts/` and `n8n/tests/`

**Interfaces:**
- Consumes: Remediated site, master workflow and three pillar articles.
- Produces: Four reviewed pilot drafts and a documented activate/continue-draft decision.

- [ ] **Step 1: Create the pilot checklist**

Every draft must pass: niche relevance, no duplicate, title 35–60, description 120–155, source verification, at least one commercial link, at least three FAQ, no broken links, image alt, correct category/tags, no console/PHP errors and successful preview.

- [ ] **Step 2: Create the pilot log schema**

Use: `executionId,scheduledAt,draftId,topic,duplicateCheck,sourceCheck,metadataCheck,linkCheck,imageCheck,reviewer,defects,reviewMinutes,publishedAt,result`.

- [ ] **Step 3: Run the complete local verification**

Run:

```powershell
node --test n8n/tests/*.test.mjs
powershell -ExecutionPolicy Bypass -File audit/scripts/capture-public-baseline.ps1
powershell -ExecutionPolicy Bypass -File audit/scripts/export-editorial-inventory.ps1
```

Expected: all Node tests pass; six target pages return 200 with valid metadata; malformed title count is zero.

- [ ] **Step 4: Generate four scheduled drafts over two weeks**

Tuesday and Thursday executions must produce exactly one draft each. Review every draft before manual publication. Any critical defect resets the pilot after the responsible rule is corrected.

- [ ] **Step 5: Verify production after each manual publication**

Check HTTP 200, canonical, meta title/description, Article and FAQ schema, internal links, image alt, sitemap inclusion and absence of console/PHP errors.

- [ ] **Step 6: Apply the activation gate**

Activate automatic publication only when all four pilot drafts have zero critical defects, zero duplicates, zero out-of-niche topics, zero unsupported quantitative claims and zero unhandled execution failures.

- [ ] **Step 7: Switch status only after the gate passes**

Change:

```javascript
const STATUS = 'draft';
```

to:

```javascript
const STATUS = 'publish';
```

Export the workflow, rerun contract tests with an explicit `AUTOMATION_APPROVED=true` test condition and save the approval evidence in `ops/activation-decision.md`.

- [ ] **Step 8: Define automatic rollback**

Any duplicate, out-of-niche publication, missing source, invalid metadata or unhandled error returns `STATUS` to `draft` before the next schedule and starts a new four-draft validation window.

Final snapshot: `audit/backups/<timestamp>/final/` containing workflows, metadata map, redirect map, inventories, pillar articles, pilot log and activation decision.

## Final Acceptance Checklist

- [ ] One master n8n workflow remains active; split workflows are archived.
- [ ] n8n remains available without an open terminal.
- [ ] Duplicate, niche, source, metadata and link checks block invalid drafts.
- [ ] Error handling records every terminal failure without exposing sensitive headers.
- [ ] Four service pages have complete meta-descriptions.
- [ ] Home and Blog titles are complete and at most 60 characters.
- [ ] All malformed WordPress titles are corrected without changing slugs.
- [ ] Every retired or merged URL has a tested 301 destination.
- [ ] The duplicate static HTML redirects to the canonical article.
- [ ] Custom public endpoints have a recorded owner, consumer and disposition.
- [ ] Three pillar articles are published with verified sources and current commercial links.
- [ ] The two-week pilot produced four acceptable drafts.
- [ ] Automatic publication is enabled only with recorded approval; otherwise the system remains in `draft` mode.
