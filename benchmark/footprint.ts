import { mkdirSync, readFileSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';
import { contenders } from './contenders';

// What each API needs to serve the benchmark: every file in its package except composer.json,
// counted in non-empty lines, plus what the project has to configure outside the package.
// Usage: bun benchmark/footprint.ts

export interface Footprint {
  files: Array<{ path: string; lines: number }>;
  lines: number;
  classes: number;
  outside: string[];
}

export function measureFootprints(): Record<string, Footprint> {
  const root = join(import.meta.dir, '..');
  const result: Record<string, Footprint> = {};
  for (const contender of contenders) {
    const dir = join(root, contender.package);
    const files = [...new Bun.Glob('**/*').scanSync({ cwd: dir })]
      .filter((file) => file !== 'composer.json')
      .sort()
      .map((path) => ({ path, lines: readFileSync(join(dir, path), 'utf8').split('\n').filter((line) => line.trim() !== '').length }));
    result[contender.key] = {
      files,
      lines: files.reduce((sum, file) => sum + file.lines, 0),
      classes: files.filter((file) => file.path.startsWith('Classes/')).length,
      outside: contender.projectConfig,
    };
  }
  return result;
}

if (import.meta.main) {
  const footprints = measureFootprints();
  const outDir = join(import.meta.dir, 'results');
  mkdirSync(outDir, { recursive: true });
  writeFileSync(join(outDir, 'footprint.json'), JSON.stringify(footprints, null, 2));

  console.log('| API | Files | Lines | PHP classes | Outside the package |');
  console.log('|---|---:|---:|---:|---|');
  for (const contender of contenders) {
    const footprint = footprints[contender.key];
    console.log(`| ${contender.label} | ${footprint.files.length} | ${footprint.lines} | ${footprint.classes} | ${footprint.outside.join('<br>') || '–'} |`);
  }
}
