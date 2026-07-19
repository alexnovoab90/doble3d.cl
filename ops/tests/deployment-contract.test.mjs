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
