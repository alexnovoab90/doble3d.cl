# cPanel Staging and SQL Rollback Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Version the verified Doble 3D theme, enable a non-public cPanel staging deployment, and document/test the database rollback boundary before remote WordPress edits.

**Architecture:** The production theme snapshot is copied byte-for-byte from the ignored FTP backup into `wordpress/theme/doble3d/` and protected by a SHA-256 manifest. cPanel deploys only that directory to `/home50/ddcl/deploy-staging/doble3d-theme/`; SQL remains ignored and is governed by a row-level-first rollback runbook.

**Tech Stack:** WordPress PHP theme files, Node.js built-in test runner, SHA-256, cPanel `.cpanel.yml`, Git.

## Global Constraints

- Never deploy this phase to `public_html`.
- Never track SQL, WordPress uploads, caches, logs, `wp-config.php`, `.env`, private keys, or credentials.
- Preserve all 36 theme files byte-for-byte before functional edits.
- Keep the full SQL dump as emergency recovery only; prefer per-batch rollback payloads.
- Make no production WordPress or database writes in this plan.

---

### Task 1: Version and verify the production theme snapshot

**Files:**
- Create: `ops/tests/theme-snapshot.test.mjs`
- Create: `ops/scripts/build-theme-manifest.mjs`
- Create: `wordpress/theme/doble3d/**`
- Create: `wordpress/theme/doble3d.manifest.csv`

**Interfaces:**
- Consumes: ignored FTP snapshot at `audit/backups/20260718-181139/public_html/wp-content/themes/doble3d/`.
- Produces: 36 tracked theme files and a CSV with columns `path,bytes,sha256`.

- [ ] **Step 1: Write the failing snapshot test**

```js
import test from 'node:test';
import assert from 'node:assert/strict';
import { createHash } from 'node:crypto';
import { readFile, readdir, stat } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const repo = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
const theme = path.join(repo, 'wordpress/theme/doble3d');
const manifestPath = path.join(repo, 'wordpress/theme/doble3d.manifest.csv');

async function walk(dir, prefix = '') {
  const entries = await readdir(dir, { withFileTypes: true });
  const files = [];
  for (const entry of entries) {
    const relative = path.posix.join(prefix, entry.name);
    const absolute = path.join(dir, entry.name);
    if (entry.isDirectory()) files.push(...await walk(absolute, relative));
    if (entry.isFile()) files.push(relative);
  }
  return files.sort();
}

test('tracks the complete production theme snapshot', async () => {
  const files = await walk(theme);
  assert.equal(files.length, 36);
  for (const required of ['style.css', 'functions.php', 'front-page.php', 'assets/js/landing.js']) {
    assert.ok(files.includes(required), `missing ${required}`);
  }
});

test('theme manifest matches every tracked byte', async () => {
  const lines = (await readFile(manifestPath, 'utf8')).trim().split(/\r?\n/);
  assert.equal(lines.shift(), 'path,bytes,sha256');
  assert.equal(lines.length, 36);
  for (const line of lines) {
    const [relative, expectedBytes, expectedHash] = line.split(',');
    const absolute = path.join(theme, ...relative.split('/'));
    const bytes = await readFile(absolute);
    assert.equal((await stat(absolute)).size, Number(expectedBytes));
    assert.equal(createHash('sha256').update(bytes).digest('hex'), expectedHash);
  }
});
```

- [ ] **Step 2: Run the test and verify RED**

Run: `node --test ops/tests/theme-snapshot.test.mjs`

Expected: FAIL because `wordpress/theme/doble3d/` and its manifest do not exist.

- [ ] **Step 3: Copy the verified snapshot mechanically**

```powershell
$source = 'audit\backups\20260718-181139\public_html\wp-content\themes\doble3d'
$destination = 'wordpress\theme\doble3d'
New-Item -ItemType Directory -Force -Path $destination | Out-Null
Get-ChildItem -LiteralPath $source -Force | Copy-Item -Destination $destination -Recurse -Force
```

- [ ] **Step 4: Add the manifest generator**

```js
import { createHash } from 'node:crypto';
import { readFile, readdir, stat, writeFile } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const repo = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
const theme = path.join(repo, 'wordpress/theme/doble3d');
const output = path.join(repo, 'wordpress/theme/doble3d.manifest.csv');

async function walk(dir, prefix = '') {
  const entries = await readdir(dir, { withFileTypes: true });
  const files = [];
  for (const entry of entries) {
    const relative = path.posix.join(prefix, entry.name);
    const absolute = path.join(dir, entry.name);
    if (entry.isDirectory()) files.push(...await walk(absolute, relative));
    if (entry.isFile()) files.push(relative);
  }
  return files.sort();
}

const rows = ['path,bytes,sha256'];
for (const relative of await walk(theme)) {
  const absolute = path.join(theme, ...relative.split('/'));
  const bytes = await readFile(absolute);
  const size = (await stat(absolute)).size;
  const hash = createHash('sha256').update(bytes).digest('hex');
  rows.push(`${relative},${size},${hash}`);
}
await writeFile(output, `${rows.join('\n')}\n`, 'utf8');
```

- [ ] **Step 5: Generate the manifest and verify GREEN**

Run: `node ops/scripts/build-theme-manifest.mjs`

Run: `node --test ops/tests/theme-snapshot.test.mjs`

Expected: 2 tests pass.

- [ ] **Step 6: Commit the snapshot**

```bash
git add ops/tests/theme-snapshot.test.mjs ops/scripts/build-theme-manifest.mjs wordpress/theme
git commit -m "wordpress: version verified production theme"
```

### Task 2: Add a staging-only cPanel deployment contract

**Files:**
- Create: `ops/tests/deployment-contract.test.mjs`
- Create: `.cpanel.yml`

**Interfaces:**
- Consumes: `wordpress/theme/doble3d/` from Task 1.
- Produces: cPanel tasks that copy only the theme to `/home50/ddcl/deploy-staging/doble3d-theme/`.

- [ ] **Step 1: Write the failing deployment contract**

```js
import test from 'node:test';
import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const repo = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
const deploymentPath = path.join(repo, '.cpanel.yml');

test('deploys only the versioned theme to non-public staging', async () => {
  const yaml = await readFile(deploymentPath, 'utf8');
  assert.match(yaml, /^---\r?\n/);
  assert.match(yaml, /\/home50\/ddcl\/deploy-staging\/doble3d-theme\//);
  assert.match(yaml, /wordpress\/theme\/doble3d\/\./);
  assert.doesNotMatch(yaml, /public_html/i);
  assert.doesNotMatch(yaml, /wp-config|uploads|audit\/backups|\.sql|mysql/i);
  assert.doesNotMatch(yaml, /\brm\b|--delete/i);
});
```

- [ ] **Step 2: Run the test and verify RED**

Run: `node --test ops/tests/deployment-contract.test.mjs`

Expected: FAIL because `.cpanel.yml` does not exist.

- [ ] **Step 3: Implement the minimal staging deployment**

```yaml
---
deployment:
  tasks:
    - export DEPLOYPATH=/home50/ddcl/deploy-staging/doble3d-theme/
    - /bin/mkdir -p "$DEPLOYPATH"
    - /bin/cp -a wordpress/theme/doble3d/. "$DEPLOYPATH"
```

- [ ] **Step 4: Verify GREEN with the snapshot tests**

Run: `node --test ops/tests/theme-snapshot.test.mjs ops/tests/deployment-contract.test.mjs`

Expected: 3 tests pass.

- [ ] **Step 5: Commit the deployment contract**

```bash
git add .cpanel.yml ops/tests/deployment-contract.test.mjs
git commit -m "ops: add cpanel staging-only deployment"
```

### Task 3: Lock down the SQL rollback procedure

**Files:**
- Create: `ops/tests/sql-rollback-contract.test.mjs`
- Create: `ops/sql-rollback-runbook.md`

**Interfaces:**
- Consumes: ignored dump at `audit/backups/20260718-181139/database/ddcl_wp950-before-remediation.sql`.
- Produces: a tested operational rule that preserves the dump outside Git and uses row-level rollback first.

- [ ] **Step 1: Write the failing SQL rollback contract**

```js
import test from 'node:test';
import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import { spawnSync } from 'node:child_process';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const repo = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
const dump = 'audit/backups/20260718-181139/database/ddcl_wp950-before-remediation.sql';

test('keeps the production SQL dump outside Git', () => {
  const ignored = spawnSync('git', ['check-ignore', '-q', dump], { cwd: repo });
  assert.equal(ignored.status, 0);
  const tracked = spawnSync('git', ['ls-files', '--error-unmatch', dump], { cwd: repo });
  assert.notEqual(tracked.status, 0);
});

test('documents row-level rollback before full restore', async () => {
  const runbook = await readFile(path.join(repo, 'ops/sql-rollback-runbook.md'), 'utf8');
  const rowLevel = runbook.indexOf('Rollback puntual por lote');
  const fullRestore = runbook.indexOf('Restauración completa de emergencia');
  assert.ok(rowLevel >= 0);
  assert.ok(fullRestore > rowLevel);
  assert.match(runbook, /SHA-256/i);
  assert.match(runbook, /staging/i);
  assert.match(runbook, /no debe agregarse a Git/i);
});
```

- [ ] **Step 2: Run the test and verify RED**

Run: `node --test ops/tests/sql-rollback-contract.test.mjs`

Expected: the Git exclusion test passes and the runbook test fails because the runbook is absent.

- [ ] **Step 3: Write the operational runbook**

Create `ops/sql-rollback-runbook.md` with these exact sections:

```markdown
# Runbook de rollback SQL de WordPress

## Respaldo base

El dump completo está en `audit/backups/20260718-181139/database/ddcl_wp950-before-remediation.sql`. Es sensible, no debe agregarse a Git ni copiarse a `public_html`. Antes de usarlo se debe verificar su tamaño, su SHA-256 y la presencia de las tablas de posts, metadatos y opciones.

## Rollback puntual por lote

Antes de cada escritura REST se guarda en `audit/baseline/wordpress-rollback/<fecha-utc>/` un JSON con ID, URL, slug, estado, título y metadatos SEO actuales. La reversión envía únicamente esos valores previos al mismo recurso. Después se comprueba HTTP 200, URL sin cambios y HTML público.

## Restauración completa de emergencia

La importación completa solo se usa si la reversión puntual no recupera el sitio. Primero se restaura el dump en una base de staging y se valida WordPress. Restaurarlo directamente en producción puede sobrescribir cambios posteriores al respaldo y requiere una nueva exportación inmediatamente anterior a la operación.

## Evidencia mínima

Cada lote registra fecha UTC, IDs afectados, valores antes/después, respuestas HTTP, verificación pública y decisión de continuar o revertir.
```

- [ ] **Step 4: Verify GREEN**

Run: `node --test ops/tests/sql-rollback-contract.test.mjs`

Expected: 2 tests pass.

- [ ] **Step 5: Commit the SQL boundary**

```bash
git add ops/tests/sql-rollback-contract.test.mjs ops/sql-rollback-runbook.md
git commit -m "ops: codify wordpress sql rollback boundary"
```

### Task 4: Verify, integrate, and publish

**Files:**
- Verify: all tracked repository files.
- Merge: branch `remediation` into `main` only after every gate passes.

**Interfaces:**
- Consumes: Tasks 1–3 and the existing 29 tests.
- Produces: a clean GitHub `main` with staging deployment enabled and no production deployment.

- [ ] **Step 1: Run the complete test suite**

```powershell
$tests = @()
$tests += Get-ChildItem n8n\tests -Filter *.test.mjs | Select-Object -ExpandProperty FullName
$tests += Get-ChildItem content\tests -Filter *.test.mjs | Select-Object -ExpandProperty FullName
$tests += Get-ChildItem ops\tests -Filter *.test.mjs | Select-Object -ExpandProperty FullName
node --test $tests
node --check n8n\scripts\consolidate-workflow.mjs
```

Expected: 34 tests pass and the syntax check exits 0.

- [ ] **Step 2: Verify repository safety**

Run: `git diff --check`

Run: the full-history secret scan used before the initial GitHub publication.

Expected: no whitespace errors, zero secret findings, SQL ignored, both worktrees clean after commits.

- [ ] **Step 3: Compare the tracked snapshot to the FTP backup**

Run a SHA-256 comparison over all 36 relative paths.

Expected: zero missing, extra, or mismatched files.

- [ ] **Step 4: Integrate and publish**

```bash
git -C "D:/sitio web" merge --ff-only remediation
git -C "D:/sitio web" push origin main
```

Expected: `origin/main` matches local `main` and contains `.cpanel.yml`.

- [ ] **Step 5: Update cPanel without deploying production**

In cPanel Git Version Control select **Actualizar desde remoto**. Confirm `main` reaches the published commit and that **Desplegar commit HEAD** is enabled. If deployment is executed, verify files appear only in `/home50/ddcl/deploy-staging/doble3d-theme/`.
