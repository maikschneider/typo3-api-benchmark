import { readFileSync } from 'node:fs';
import { join } from 'node:path';
import { contenders, scenarios } from './contenders';

interface Meta {
  mode: string;
  vus: number;
  repetitions: number;
  versions: Record<string, string>;
  date: string;
  runs: Array<{ contender: string; scenario: string; pass: string; repetition: number; file: string }>;
}

type Metrics = Record<string, { values: Record<string, number> }>;

const median = (values: number[]): number => {
  const sorted = values.filter((value) => !Number.isNaN(value)).sort((a, b) => a - b);
  if (sorted.length === 0) return Number.NaN;
  const middle = Math.floor(sorted.length / 2);
  return sorted.length % 2 ? sorted[middle] : (sorted[middle - 1] + sorted[middle]) / 2;
};

const fmt = (value: number, digits = 1): string => (Number.isNaN(value) ? '–' : value.toFixed(digits));

export function renderReport(dir: string): string {
  const meta: Meta = JSON.parse(readFileSync(join(dir, 'meta.json'), 'utf8'));
  const lines = [
    `# API benchmark, ${meta.date.slice(0, 16).replace('T', ' ')} UTC`,
    '',
    `Mode **${meta.mode}**, ${meta.vus} concurrent virtual users, ${meta.repetitions} repetitions. ${Object.entries(meta.versions).map(([name, version]) => `${name} ${version}`).join(', ')}.`,
    '',
    'Each pass requests every URL exactly once. **miss**: first pass after flushing all caches. **hit**: the same URLs again.',
    'Every value is the median over the repetitions; the p50 column adds the range across repetitions.',
    'Latency is measured by k6 at the client; queries, app time and memory are reported by the server for the same requests.',
  ];

  for (const scenario of scenarios) {
    const scenarioRuns = meta.runs.filter((run) => run.scenario === scenario.key);
    if (scenarioRuns.length === 0) continue;

    lines.push('', `## ${scenario.label}`, '');
    lines.push('| API | Cache | req/s | p50 ms (range) | p95 ms | p99 ms | Queries | App ms | Memory MB | Payload KB | Errors |');
    lines.push('|---|---|---:|---:|---:|---:|---:|---:|---:|---:|---:|');

    for (const contender of contenders) {
      for (const pass of ['miss', 'hit']) {
        const results: Metrics[] = scenarioRuns
          .filter((run) => run.contender === contender.key && run.pass === pass)
          .map((run) => JSON.parse(readFileSync(run.file, 'utf8')).metrics);
        if (results.length === 0) continue;

        const metric = (name: string, stat: string): number => median(results.map((m) => m[name]?.values[stat] ?? Number.NaN));
        const p50s = results.map((m) => m.http_req_duration.values.med);
        const errors = median(results.map((m) => 1 - (m.checks?.values.rate ?? 0)));

        lines.push(
          `| ${contender.label} | ${pass} | ${fmt(metric('http_reqs', 'rate'), 0)} | ${fmt(median(p50s))} (${fmt(Math.min(...p50s), 0)}–${fmt(Math.max(...p50s), 0)}) | ${fmt(metric('http_req_duration', 'p(95)'))} | ${fmt(metric('http_req_duration', 'p(99)'))} | ${fmt(metric('api_queries', 'med'), 0)} | ${fmt(metric('api_app_time', 'med'))} | ${fmt(metric('api_memory_mb', 'med'))} | ${fmt(metric('api_payload_kb', 'med'))} | ${fmt(errors * 100, 0)}% |`,
        );
      }
    }
  }

  return lines.join('\n') + '\n';
}

if (import.meta.main) {
  process.stdout.write(renderReport(process.argv[2]));
}
