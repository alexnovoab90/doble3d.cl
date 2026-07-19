import test from 'node:test';
import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const repo = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');

test('public htaccess redirects the duplicate before WordPress rewrites', async () => {
  const source = await readFile(path.join(repo, 'wordpress/ftp/.htaccess'), 'utf8');
  const redirect = 'Redirect 301 /d3d_post_junio2026.html https://doble3d.cl/xr-empresarial-pico-ar-ia-junio-2026/';
  const redirectIndex = source.indexOf(redirect);
  const wordpressIndex = source.indexOf('# BEGIN WordPress');
  assert.ok(redirectIndex >= 0, 'missing duplicate article redirect');
  assert.ok(wordpressIndex >= 0, 'missing WordPress rewrite block');
  assert.ok(redirectIndex < wordpressIndex, 'redirect must run before WordPress rewrites');
});

test('image generator keeps its GET contract behind input guards', async () => {
  const source = await readFile(path.join(repo, 'wordpress/ftp/image-gen.php'), 'utf8');
  assert.match(source, /REQUEST_METHOD/);
  assert.match(source, /X-Robots-Tag: noindex, nofollow/);
  assert.match(source, /X-Content-Type-Options: nosniff/);
  assert.match(source, /'titulo'\s*=>\s*120/);
  assert.match(source, /Content-Type: image\/webp/);
});
