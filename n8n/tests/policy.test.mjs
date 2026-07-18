import test from 'node:test';
import assert from 'node:assert/strict';
import policy from '../workflow-policy.json' with { type: 'json' };
import duplicatePosts from './fixtures/duplicate-posts.json' with { type: 'json' };
import fixtureArticle from './fixtures/valid-article.json' with { type: 'json' };
import { idempotencyKey, validateArticle, validateTopic } from '../lib/policy.mjs';

const validArticle = (overrides = {}) => ({
  titulo: 'Realidad virtual para capacitación minera segura',
  slug: 'realidad-virtual-capacitacion-minera',
  meta_title: 'Realidad virtual para capacitación minera segura',
  meta_description: 'La realidad virtual permite entrenar procedimientos críticos de minería en entornos seguros, medibles y sin detener equipos productivos.',
  focus_keyword: 'realidad virtual minería chile',
  categoria: 'realidad virtual',
  tags: ['VR', 'Minería'],
  fuentes: [{ url: 'https://www.codelco.com/informacion-publica' }],
  faq: [{ q: 'Uno', a: 'Respuesta' }, { q: 'Dos', a: 'Respuesta' }, { q: 'Tres', a: 'Respuesta' }],
  content: '<p>Contenido.</p><a href="https://doble3d.cl/servicios/realidad-virtual/">Servicio VR</a>',
  ...overrides,
});

test('rejects consumer technology topics', () => {
  assert.equal(validateTopic('Precios de iPhone y MacBook 2026', policy).ok, false);
});

test('accepts industrial VR topics with or without accents', () => {
  assert.equal(validateTopic('Realidad virtual para capacitación minera', policy).ok, true);
  assert.equal(validateTopic('Realidad virtual para capacitacion minera', policy).ok, true);
});

test('rejects an article without sources and commercial links', () => {
  const result = validateArticle(validArticle({ fuentes: [], content: '<p>Contenido sin enlace comercial.</p>' }), [], policy);
  assert.equal(result.ok, false);
  assert.ok(result.errors.includes('insufficient_sources'));
  assert.ok(result.errors.includes('missing_commercial_link'));
});

test('accepts a complete, in-scope article', () => {
  assert.deepEqual(validateArticle(validArticle(), [], policy), { ok: true, errors: [] });
});

test('rejects duplicate slugs and similar titles', () => {
  const bySlug = validateArticle(validArticle(), [{ slug: 'realidad-virtual-capacitacion-minera', title: 'Otro título' }], policy);
  const byTitle = validateArticle(validArticle(), [{ slug: 'otro-slug', title: 'Capacitación minera segura con realidad virtual' }], policy);
  assert.ok(bySlug.errors.includes('duplicate_article'));
  assert.ok(byTitle.errors.includes('duplicate_article'));
});

test('builds a stable idempotency key', () => {
  assert.equal(
    idempotencyKey('2026-07-21', 'VR minería', 'realidad-virtual-mineria'),
    '2026-07-21|vr-mineria|realidad-virtual-mineria',
  );
});

test('fixtures simulate one accepted draft followed by a duplicate stop', () => {
  assert.equal(validateArticle(fixtureArticle, [], policy).ok, true);
  const secondRun = validateArticle(fixtureArticle, duplicatePosts, policy);
  assert.equal(secondRun.ok, false);
  assert.ok(secondRun.errors.includes('duplicate_article'));
  assert.equal(
    idempotencyKey('2026-07-21', 'Realidad virtual minería', fixtureArticle.slug),
    idempotencyKey('2026-07-21', 'Realidad virtual minería', fixtureArticle.slug),
  );
});
