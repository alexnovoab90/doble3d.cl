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
