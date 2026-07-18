import test from 'node:test';
import assert from 'node:assert/strict';
import { existsSync, readFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const read = relative => readFileSync(join(root, relative), 'utf8');

test('ships persistent runner, health check and registration scripts', () => {
  for (const relative of [
    'scripts/start-n8n.ps1',
    'scripts/healthcheck.ps1',
    'scripts/register-n8n-task.ps1',
    'RUNBOOK.md',
  ]) {
    assert.equal(existsSync(join(root, relative)), true, `${relative} is required`);
  }
});

test('runner uses the existing data directory and appends logs', () => {
  const source = read('scripts/start-n8n.ps1');
  assert.match(source, /n8n(?:\.cmd)?['"]?\s+start/i);
  assert.match(source, /n8n\.log/);
  assert.match(source, /\*>>/);
  assert.doesNotMatch(source, /N8N_USER_FOLDER\s*=/i);
});

test('health check requires HTTP 200 from healthz with a timeout', () => {
  const source = read('scripts/healthcheck.ps1');
  assert.match(source, /localhost:5678\/healthz/);
  assert.match(source, /TimeoutSec/);
  assert.match(source, /StatusCode\s*-ne\s*200/);
});

test('scheduled task restarts and runs with limited privileges', () => {
  const source = read('scripts/register-n8n-task.ps1');
  assert.match(source, /RestartCount\s+3/);
  assert.match(source, /RestartInterval/);
  assert.match(source, /RunLevel\s+Limited/);
  assert.match(source, /Doble3D-n8n/);
  assert.match(source, /SupportsShouldProcess/);
});

test('launcher checks health, starts the task and checks again before opening', () => {
  const source = read('iniciar-n8n.bat');
  assert.equal((source.match(/healthcheck\.ps1/gi) ?? []).length, 2);
  assert.match(source, /Start-ScheduledTask[^\r\n]*Doble3D-n8n/i);
  assert.match(source, /timeout \/t 15/i);
  assert.match(source, /start "" http:\/\/localhost:5678/i);
});

test('runbook covers operation and safe recovery', () => {
  const source = read('RUNBOOK.md');
  for (const phrase of ['Iniciar', 'Detener', 'Salud', 'Logs', 'Prueba manual', 'Desactivar', 'Restaurar', 'draft']) {
    assert.match(source, new RegExp(phrase, 'i'));
  }
});

