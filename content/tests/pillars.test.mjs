import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..', 'pillars');
const files = [
  ['realidad-virtual-mineria.md', ['/servicios/realidad-virtual/', '/servicios/core/']],
  ['animacion-3d-industrial.md', ['/servicios/animacion-3d/']],
  ['core-entrenamiento-vr.md', ['/servicios/core/', '/servicios/gamificacion-scorm/']],
];
const required = ['title', 'slug', 'metaTitle', 'metaDescription', 'focusKeyword', 'category', 'tags', 'canonical', 'author', 'reviewedAt'];

const parse = name => {
  const source = readFileSync(join(root, name), 'utf8');
  const match = source.match(/^---\r?\n([\s\S]*?)\r?\n---\r?\n([\s\S]*)$/);
  assert.ok(match, `${name} must have YAML front matter`);
  const front = Object.fromEntries(match[1].split(/\r?\n/)
    .filter(line => /^[A-Za-z][A-Za-z]+:/.test(line))
    .map(line => {
      const index = line.indexOf(':');
      return [line.slice(0, index), line.slice(index + 1).trim().replace(/^"|"$/g, '')];
    }));
  return { source, front, body: match[2] };
};

for (const [name, commercialPaths] of files) {
  test(`${name} passes editorial structure`, () => {
    const { source, front, body } = parse(name);
    for (const key of required) assert.ok(front[key], `${name} missing ${key}`);
    assert.match(source, /^sources:\r?\n(?:\s+-\s+"https:\/\/[^\r\n]+"\r?\n?)+/m, `${name} missing sources`);
    assert.ok(front.metaTitle.length >= 35 && front.metaTitle.length <= 60, `${name} meta title length`);
    assert.ok(front.metaDescription.length >= 120 && front.metaDescription.length <= 155, `${name} meta description length`);
    assert.equal((body.match(/^# /gm) ?? []).length, 1, `${name} must have one H1`);
    assert.ok((body.match(/^## /gm) ?? []).length >= 4, `${name} needs logical H2 sections`);
    assert.ok((body.match(/^### /gm) ?? []).length >= 3, `${name} needs at least three FAQ questions`);
    for (const path of commercialPaths) assert.ok(source.includes(path), `${name} missing ${path}`);
    assert.doesNotMatch(source, /\b60%\b|10[,.]000|\b4\s*[x×]\b/i, `${name} has unsupported metrics`);
    assert.match(source, /featuredImageAlt:/, `${name} needs image alt text`);
  });
}

test('source register covers every pillar with verified sources', () => {
  const source = readFileSync(join(root, 'source-register.csv'), 'utf8').trim().split(/\r?\n/);
  assert.equal(source.length, 7);
  for (const slug of ['realidad-virtual-mineria', 'animacion-3d-industrial', 'core-entrenamiento-vr']) {
    assert.ok(source.some(line => line.startsWith(`"${slug}"`)), `missing sources for ${slug}`);
  }
  assert.ok(source.slice(1).every(line => /,"2026-07-18"$/.test(line)), 'every source must have verifiedAt');
});
