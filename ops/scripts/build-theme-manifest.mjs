import { createHash } from 'node:crypto';
import { readFile, readdir, stat, writeFile } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const repo = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
const theme = path.join(repo, 'wordpress/theme/doble3d');
const output = path.join(repo, 'wordpress/theme/doble3d.manifest.csv');

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

const rows = ['path,bytes,sha256'];
for (const relative of await walk(theme)) {
  const absolute = path.join(theme, ...relative.split('/'));
  const bytes = await readFile(absolute);
  const size = (await stat(absolute)).size;
  const hash = createHash('sha256').update(bytes).digest('hex');
  rows.push(`${relative},${size},${hash}`);
}
await writeFile(output, `${rows.join('\n')}\n`, 'utf8');
