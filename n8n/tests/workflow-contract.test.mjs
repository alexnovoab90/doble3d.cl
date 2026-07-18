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
  const isDraft = /const STATUS = 'draft'/.test(node('Ensamblar post').parameters.jsCode);
  assert.equal(isDraft, true, 'Ensamblar post must set STATUS to draft');
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
