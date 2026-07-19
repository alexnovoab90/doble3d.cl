import test from 'node:test';
import assert from 'node:assert/strict';
import { createHash } from 'node:crypto';
import { readFile, readdir, stat } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const repo = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
const theme = path.join(repo, 'wordpress/theme/doble3d');
const manifestPath = path.join(repo, 'wordpress/theme/doble3d.manifest.csv');
const attributesPath = path.join(repo, '.gitattributes');
const metadataMapPath = path.join(repo, 'wordpress/seo/metadata-map.csv');

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

test('disables text conversion for the theme snapshot', async () => {
  const lines = (await readFile(attributesPath, 'utf8')).trim().split(/\r?\n/);
  assert.ok(lines.includes('wordpress/theme/doble3d/** -text'));
});

test('theme manifest matches every tracked byte', async () => {
  const lines = (await readFile(manifestPath, 'utf8')).trim().split(/\r?\n/);
  assert.equal(lines.shift(), 'path,bytes,sha256');
  assert.equal(lines.length, 36);
  const manifestPaths = lines.map((line) => line.split(',')[0]);
  assert.equal(new Set(manifestPaths).size, manifestPaths.length);
  assert.deepEqual(manifestPaths, await walk(theme));
  for (const line of lines) {
    const [relative, expectedBytes, expectedHash] = line.split(',');
    const absolute = path.join(theme, ...relative.split('/'));
    const bytes = await readFile(absolute);
    assert.equal((await stat(absolute)).size, Number(expectedBytes));
    assert.equal(createHash('sha256').update(bytes).digest('hex'), expectedHash);
  }
});

test('home Yoast filters use the approved metadata map copy', async () => {
  const functions = await readFile(path.join(theme, 'functions.php'), 'utf8');
  const metadataMap = await readFile(metadataMapPath, 'utf8');
  const expectedTitle = 'Doble 3D | Realidad Virtual y Animación 3D Industrial';
  const expectedDescription = 'Soluciones de realidad virtual y animación 3D para minería e industria en Chile. Entrena procedimientos críticos sin detener la faena.';
  assert.match(metadataMap, new RegExp(expectedTitle.replace(/[|]/g, '\\|')));
  assert.match(metadataMap, new RegExp(expectedDescription.replace(/[.]/g, '\\.')));
  assert.match(functions, new RegExp(`\\$d3d_home_seo_title\\s*=\\s*'${expectedTitle.replace(/[|]/g, '\\|')}'`));
  assert.match(functions, new RegExp(`\\$d3d_home_seo_desc\\s*=\\s*'${expectedDescription.replace(/[.]/g, '\\.')}'`));
});
