import { mkdirSync, rmSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';
import { contenders } from './contenders';
import { get } from './http';
import { activate, deactivate } from './isolation';

// One T3Flare profile per API for the same cold collection request: where the time goes in
// TYPO3 terms, which queries repeat, how much code loads. Cached requests are not profiled,
// because the profiler's request does not share the cache key of a normal request.
// Needs dev mode (T3Flare is a dev dependency).
// Usage: bun benchmark/profile.ts

const root = join(import.meta.dir, '..');
const outDir = join(import.meta.dir, 'results', 'profiles');
rmSync(outDir, { recursive: true, force: true });
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
  activate(contender);
  let json: string;
  try {
    ddev('exec', 'vendor/bin/typo3', 'cache:flush');
    await get(contender.list(400, 20));
    ddev('t3flare', contender.list(7, 20));
    json = ddev('exec', 'vendor/bin/typo3', 't3flare:report', 'latest', '--format=json');
  } finally {
    deactivate(contender);
  }
  writeFileSync(join(outDir, `${contender.key}.json`), json);
  const report: Report = JSON.parse(json);
  const largest = report.units.filter((unit) => !unit.unit.startsWith('bootstrap:')).sort((a, b) => b.ms - a.ms)[0];

  rows.push(`| ${contender.label} | ${report.summary.wallMs.toFixed(0)} | ${report.summary.queries} | ${(report.summary.peakMemory / 1048576).toFixed(1)} | ${report.summary.includedFiles} | \`${largest?.unit}\` ${largest?.ms.toFixed(0)} ms, ${largest?.queries} queries |`);
  for (const finding of report.findings) {
    findings.push(`- **${contender.label}** (${finding.severity}): ${finding.title}`);
  }
  console.error(`${contender.label} profiled`);
}

const markdown = [
  '| API | Wall ms | Queries | Peak MB | Included files | Largest unit |',
  '|---|---:|---:|---:|---:|---|',
  ...rows,
  '',
  'Findings:',
  '',
  ...findings,
  '',
  'Wall time includes the sampling overhead of the profiler; compare it between APIs, not with the load test.',
].join('\n');

writeFileSync(join(outDir, 'profiles.md'), markdown + '\n');
console.log(markdown);
