import { join } from 'node:path';
import { contenders, suites } from './contenders';
import { latestRunDir, readLoadRun } from './load-results';

const fmt = (value: number, digits = 1): string => (Number.isNaN(value) ? '–' : value.toFixed(digits));

export function renderReport(dir: string): string {
  const { meta, results } = readLoadRun(dir);
  const lines = [
    `# API benchmark, ${meta.date.slice(0, 16).replace('T', ' ')} UTC`,
    '',
    `Mode **${meta.mode}**, ${meta.repetitions} repetitions, ${meta.vus} virtual users unless a point sets its own. ${Object.entries(meta.versions).map(([name, version]) => `${name} ${version}`).join(', ')}.`,
    '',
    'Each pass requests every URL exactly once. **miss**: first pass after flushing all caches. **hit**: the same URLs again.',
    'Every value is the median over the repetitions; the p50 column adds the range across repetitions.',
  ];

  for (const suite of suites) {
    if (!results[suite.key]) continue;
    lines.push('', `## ${suite.label}`, '', suite.question);
    for (const point of suite.points) {
      if (!results[suite.key][point.key]) continue;
      lines.push('', `### ${point.label} (${point.useCase})`, '');
      lines.push('| API | Cache | req/s | p50 ms (range) | p95 ms | p99 ms | Queries | App ms | Memory MB | Payload KB | Errors |');
      lines.push('|---|---|---:|---:|---:|---:|---:|---:|---:|---:|---:|');
      for (const contender of contenders) {
        for (const pass of ['miss', 'hit']) {
          const m = results[suite.key][point.key][contender.key]?.[pass];
          if (!m) continue;
          lines.push(`| ${contender.label} | ${pass} | ${fmt(m.rps, 0)} | ${fmt(m.p50)} (${fmt(m.p50Min, 0)}–${fmt(m.p50Max, 0)}) | ${fmt(m.p95)} | ${fmt(m.p99)} | ${fmt(m.queries, 0)} | ${fmt(m.appMs)} | ${fmt(m.memoryMb)} | ${fmt(m.payloadKb)} | ${fmt(m.errorRate * 100, 0)}% |`);
        }
      }
    }
  }

  return lines.join('\n') + '\n';
}

if (import.meta.main) {
  const dir = process.argv[2] ?? latestRunDir(join(import.meta.dir, 'results'));
  if (!dir) throw new Error('No load run found in benchmark/results/');
  process.stdout.write(renderReport(dir));
}
