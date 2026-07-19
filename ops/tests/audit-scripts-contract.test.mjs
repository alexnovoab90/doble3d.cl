import test from 'node:test';
import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const repo = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
const scripts = [
  'audit/scripts/capture-public-baseline.ps1',
  'audit/scripts/export-editorial-inventory.ps1',
  'audit/scripts/build-editorial-matrix.ps1',
];

test('audit scripts resolve default output directories after parameter binding', async () => {
  for (const relativePath of scripts) {
    const source = await readFile(path.join(repo, relativePath), 'utf8');
    assert.doesNotMatch(
      source,
      /\[string\]\$OutputDir\s*=\s*\(Join-Path\s+\$PSScriptRoot/i,
      `${relativePath} must not read PSScriptRoot from a parameter default`,
    );
    assert.match(
      source,
      /if\s*\(\[string\]::IsNullOrWhiteSpace\(\$OutputDir\)\)/i,
      `${relativePath} must assign its default output directory in the script body`,
    );
  }
});
