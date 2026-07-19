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

test('requires every batch rollback capture to record the base dump hash', async () => {
  const runbook = await readFile(path.join(repo, 'ops/sql-rollback-runbook.md'), 'utf8');
  const rowLevelSection = runbook.slice(
    runbook.indexOf('Rollback puntual por lote'),
    runbook.indexOf('Restauración completa de emergencia'),
  );
  assert.match(rowLevelSection, /cada captura[^.]*hash SHA-256 del dump base/i);
});

test('operational docs acknowledge the validated SQL backup', async () => {
  const auditReadme = await readFile(path.join(repo, 'audit/README.md'), 'utf8');
  const activationDecision = await readFile(path.join(repo, 'ops/activation-decision.md'), 'utf8');
  assert.doesNotMatch(auditReadme, /export SQL todavía no está disponible/i);
  assert.doesNotMatch(activationDecision, /Falta un export SQL completo/i);
  assert.match(auditReadme, /6\.990\.049 bytes/i);
  assert.match(activationDecision, /export SQL completo y validado/i);
});
