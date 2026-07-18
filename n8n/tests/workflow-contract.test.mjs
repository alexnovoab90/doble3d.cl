import test from 'node:test';
import assert from 'node:assert/strict';
import { existsSync, readFileSync, readdirSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const workflow = JSON.parse(readFileSync(join(root, 'auto-post-doble3d.json'), 'utf8'));
const node = name => workflow.nodes.find(item => item.name === name);
const targets = name => (workflow.connections[name]?.main ?? [])
  .flat()
  .map(connection => connection.node);

test('uses the Santiago Tuesday/Thursday schedule', () => {
  assert.equal(workflow.settings.timezone, 'America/Santiago');
  const schedules = workflow.nodes.filter(item => item.type === 'n8n-nodes-base.scheduleTrigger');
  assert.equal(schedules.length, 1);
  assert.equal(schedules[0].parameters.rule.interval[0].expression, '0 11 * * 2,4');
});

test('keeps the pilot in draft mode', () => {
  const approved = process.env.AUTOMATION_APPROVED === 'true';
  const expected = approved ? 'publish' : 'draft';
  const hasExpectedStatus = new RegExp(`const STATUS = '${expected}'`).test(node('Ensamblar post').parameters.jsCode);
  assert.equal(hasExpectedStatus, true, `Ensamblar post must set STATUS to ${expected}`);
});

test('contains duplicate and article gates', () => {
  assert.ok(node('Buscar duplicado'));
  assert.ok(node('Validar candidato'));
  assert.ok(node('Validar artículo'));
  assert.ok(node('Buscar slug final'));
});

test('requires structured sources and content from the model', () => {
  const prompt = node('Preparar prompt').parameters.jsCode;
  for (const field of ['fuentes', 'content', 'titulo_imagen']) {
    assert.equal(prompt.includes(`"${field}"`), true, `prompt must require ${field}`);
  }
});

test('validates once, requests at most one correction and never truncates metadata', () => {
  assert.ok(node('¿Requiere corrección?'));
  assert.ok(node('DeepSeek (corregir)'));
  assert.ok(node('Validar artículo corregido'));
  const assembly = node('Ensamblar post').parameters.jsCode;
  assert.equal(/trunc\s*\(/.test(assembly), false, 'metadata must be rejected, not truncated');
});

test('keeps the exported workflow inactive', () => {
  assert.equal(workflow.active, false);
});

test('all Code node bodies are syntactically valid JavaScript', () => {
  const AsyncFunction = Object.getPrototypeOf(async function () {}).constructor;
  for (const item of workflow.nodes.filter(candidate => candidate.type === 'n8n-nodes-base.code')) {
    assert.doesNotThrow(
      () => new AsyncFunction(item.parameters.jsCode),
      `invalid JavaScript in Code node: ${item.name}`,
    );
  }
});

test('wires preflight, one correction branch and final slug guard in order', () => {
  assert.deepEqual(targets('Elegir tema'), ['Buscar duplicado']);
  assert.deepEqual(targets('Buscar duplicado'), ['Validar candidato']);
  assert.deepEqual(targets('Validar candidato'), ['Noticias RSS']);
  assert.deepEqual(targets('DeepSeek (redactar)'), ['Validar artículo']);
  assert.deepEqual(targets('¿Requiere corrección?'), ['DeepSeek (corregir)', 'Artículo aprobado']);
  assert.deepEqual(targets('DeepSeek (corregir)'), ['Validar artículo corregido']);
  assert.deepEqual(targets('Validar artículo corregido'), ['Buscar slug final']);
  assert.deepEqual(targets('Artículo aprobado'), ['Buscar slug final']);
  assert.deepEqual(targets('Buscar slug final'), ['Confirmar slug final']);
  assert.deepEqual(targets('Confirmar slug final'), ['Ensamblar post']);
});

test('configures bounded retries only on retry-safe HTTP operations', () => {
  const retrySafe = [
    'Noticias RSS', 'Buscar duplicado', 'DeepSeek (redactar)', 'DeepSeek (corregir)',
    'Buscar slug final', 'Generar imagen', 'Alt de la imagen',
  ];
  for (const name of retrySafe) {
    assert.equal(node(name).retryOnFail, true, `${name} must retry`);
    assert.equal(node(name).maxTries, 3, `${name} must have bounded retries`);
    assert.equal(node(name).waitBetweenTries, 5000, `${name} must wait between retries`);
  }
  for (const name of ['Subir imagen a WP', 'Publicar en WordPress']) {
    assert.notEqual(node(name).retryOnFail, true, `${name} must not retry a non-idempotent POST blindly`);
  }
});

test('links an inactive, redacted error workflow', () => {
  const errorPath = join(root, 'error-handler-doble3d.json');
  assert.equal(existsSync(errorPath), true, 'error workflow export is required');
  const errorWorkflow = JSON.parse(readFileSync(errorPath, 'utf8'));
  assert.equal(workflow.settings.errorWorkflow, errorWorkflow.id);
  assert.equal(errorWorkflow.active, false);
  assert.deepEqual(
    errorWorkflow.nodes.map(item => item.name),
    ['Error Trigger', 'Formatear error', 'Send Email'],
  );
  const formatter = errorWorkflow.nodes.find(item => item.name === 'Formatear error').parameters.jsCode;
  assert.equal(/authorization|headers|requestBody/i.test(formatter), false);
});

test('queries the final slug with edit context immediately before assembly', () => {
  const query = node('Buscar slug final').parameters.queryParameters.parameters;
  assert.ok(query.some(item => item.name === 'context' && item.value === 'edit'));
  assert.deepEqual(targets('Buscar slug final'), ['Confirmar slug final']);
  assert.deepEqual(targets('Confirmar slug final'), ['Ensamblar post']);
});

test('keeps only the master export in the n8n root', () => {
  const exports = readdirSync(root).filter(name => /^auto-post.*\.json$/.test(name));
  assert.deepEqual(exports, ['auto-post-doble3d.json']);
});
