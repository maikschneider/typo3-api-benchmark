import { mkdirSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';
import { contenders } from './contenders';

// One T3Flare profile per API for the same collection request, once on a cold cache and once
// on a warm one: where the time goes in TYPO3 terms, which queries repeat, how much code loads.
// Needs dev mode (T3Flare is a dev dependency).
// Usage: bun benchmark/profile.ts

const root = join(import.meta.dir, '..');
const outDir = join(import.meta.dir, 'results', 'profiles');
mkdirSync(outDir, { recursive: true });

const ddev = (...cmd: string[]): string => {
  const result = Bun.spawnSync(['ddev', ...cmd], { cwd: root, stderr: 'pipe' });
  if (result.exitCode !== 0) throw new Error(`ddev ${cmd.join(' ')}: ${result.stderr}`);
  return result.stdout.toString();
};

interface Report {
  profile: string;
  summary: { wallMs: number; queries: number; peakMemory: number; includedFiles: number };
  units: Array<{ unit: string; ms: number; queries: number }>;
  findings: Array<{ title: string; severity: string }>;
}

const rows: string[] = [];
const findings: string[] = [];

for (const contender of contenders) {
  ddev('exec', 'vendor/bin/typo3', 'cache:flush');
  const path = contender.list(7, 20);
  for (const pass of ['miss', 'hit']) {
    ddev('t3flare', path);
    const json = ddev('exec', 'vendor/bin/typo3', 't3flare:report', 'latest', '--format=json');
    writeFileSync(join(outDir, `${contender.key}-${pass}.json`), json);
    const report: Report = JSON.parse(json);
    const api = report.units.filter((unit) => !unit.unit.startsWith('bootstrap:')).sort((a, b) => b.ms - a.ms)[0];
    rows.push(`| ${contender.label} | ${pass} | ${report.summary.wallMs.toFixed(0)} | ${report.summary.queries} | ${(report.summary.peakMemory / 1048576).toFixed(1)} | ${report.summary.includedFiles} | \`${api?.unit.replace('middleware: ', '')}\` ${api?.ms.toFixed(0)} ms, ${api?.queries} queries | \`${report.profile}\` |`);
    if (pass === 'miss') {
      for (const finding of report.findings) {
        findings.push(`- **${contender.label}** (${finding.severity}): ${finding.title}`);
      }
    }
  }
  console.error(`${contender.label} profiled`);
}

const markdown = [
  '| API | Cache | Wall ms | Queries | Peak MB | Included files | Largest unit | Profile |',
  '|---|---|---:|---:|---:|---:|---|---|',
  ...rows,
  '',
  'Findings on the cold request:',
  '',
  ...findings,
  '',
  'Wall time includes the sampling overhead of the profiler; compare it between APIs, not with the load test.',
  'Flame graphs: `var/t3flare/<profile>.collapsed.txt` opens in https://www.speedscope.app.',
].join('\n');

writeFileSync(join(outDir, 'profiles.md'), markdown + '\n');
console.log(markdown);
