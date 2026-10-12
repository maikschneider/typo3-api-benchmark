import { readFileSync } from 'node:fs';
import { join, relative } from 'node:path';
import { contenders } from './contenders';

// What each API needs to serve the benchmark: every file in its package except composer.json,
// counted in non-empty lines, plus what the project has to configure outside the package.
// Usage: bun benchmark/footprint.ts

const root = join(import.meta.dir, '..');

const lines = ['| API | Files | Lines | PHP classes | Outside the package | Package files |', '|---|---:|---:|---:|---|---|'];

for (const contender of contenders) {
  const dir = join(root, contender.package);
  const files = [...new Bun.Glob('**/*').scanSync({ cwd: dir, dot: false })].filter((file) => file !== 'composer.json').sort();
  const lineCount = files.reduce((sum, file) => sum + readFileSync(join(dir, file), 'utf8').split('\n').filter((line) => line.trim() !== '').length, 0);
  const classes = files.filter((file) => file.startsWith('Classes/')).length;
  const outside = contender.projectConfig.length > 0 ? contender.projectConfig.join('<br>') : '–';
  lines.push(`| ${contender.label} | ${files.length} | ${lineCount} | ${classes} | ${outside} | ${files.map((file) => `\`${file}\``).join('<br>')} |`);
}

console.log(lines.join('\n'));
console.error(`Counted relative to ${relative(process.cwd(), root) || '.'}`);
