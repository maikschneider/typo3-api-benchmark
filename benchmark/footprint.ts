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
  /** Files that exist only for the depth sweep; not part of files, lines and classes. */
  depth: { files: Array<{ path: string; lines: number }>; lines: number };
}

export function measureFootprints(): Record<string, Footprint> {
  const root = join(import.meta.dir, '..');
  const result: Record<string, Footprint> = {};
  for (const contender of contenders) {
    const dir = join(root, contender.package);
    const all = [...new Bun.Glob('**/*').scanSync({ cwd: dir })]
      .filter((file) => file !== 'composer.json')
      .sort()
      .map((path) => ({ path, lines: readFileSync(join(dir, path), 'utf8').split('\n').filter((line) => line.trim() !== '').length }));
    const isDepth = (path: string): boolean => contender.depthFiles.some((pattern) => new Bun.Glob(pattern).match(path));
    const files = all.filter((file) => !isDepth(file.path));
    const depthFiles = all.filter((file) => isDepth(file.path));
    result[contender.key] = {
      files,
      lines: files.reduce((sum, file) => sum + file.lines, 0),
      classes: files.filter((file) => file.path.startsWith('Classes/')).length,
      outside: contender.projectConfig,
      depth: { files: depthFiles, lines: depthFiles.reduce((sum, file) => sum + file.lines, 0) },
    };
  }
  return result;
}

if (import.meta.main) {
  const footprints = measureFootprints();
  const outDir = join(import.meta.dir, 'results');
  mkdirSync(outDir, { recursive: true });
  writeFileSync(join(outDir, 'footprint.json'), JSON.stringify(footprints, null, 2));

  console.log('| API | Files | Lines | PHP classes | Outside the package | Depth sweep variants |');
  console.log('|---|---:|---:|---:|---|---|');
  for (const contender of contenders) {
    const footprint = footprints[contender.key];
    console.log(`| ${contender.label} | ${footprint.files.length} | ${footprint.lines} | ${footprint.classes} | ${footprint.outside.join('<br>') || '–'} | ${footprint.depth.files.length} ${footprint.depth.files.length === 1 ? 'file' : 'files'}, ${footprint.depth.lines} lines |`);
  }
}
