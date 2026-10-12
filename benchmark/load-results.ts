import { existsSync, readdirSync, readFileSync } from 'node:fs';
import { join } from 'node:path';

export interface LoadMeta {
  mode: string;
  vus: number;
  repetitions: number;
  versions: Record<string, string>;
  date: string;
  runs: Array<{ suite?: string; point?: string; scenario?: string; contender: string; pass: string; repetition: number; vus?: number; file: string }>;
}

export interface LoadMetrics {
  rps: number;
  p50: number;
  p50Min: number;
  p50Max: number;
  p95: number;
  p99: number;
  queries: number;
  appMs: number;
  memoryMb: number;
  payloadKb: number;
  errorRate: number;
}

/** suite → point → contender → pass (miss/hit) → metrics, each the median over the repetitions */
export type LoadResults = Record<string, Record<string, Record<string, Record<string, LoadMetrics>>>>;

type K6Metrics = Record<string, { values: Record<string, number> }>;

const median = (values: number[]): number => {
  const sorted = values.filter((value) => !Number.isNaN(value)).sort((a, b) => a - b);
  if (sorted.length === 0) return Number.NaN;
  const middle = Math.floor(sorted.length / 2);
  return sorted.length % 2 ? sorted[middle] : (sorted[middle - 1] + sorted[middle]) / 2;
};

/** Newest load run, preferring prod over dev runs. */
export function latestRunDir(resultsDir: string): string | null {
  if (!existsSync(resultsDir)) return null;
  const runs = readdirSync(resultsDir).filter((name) => /^\d{4}-.*-(prod|dev)$/.test(name) && existsSync(join(resultsDir, name, 'meta.json'))).sort();
  const prod = runs.filter((name) => name.endsWith('-prod'));
  const pick = (prod.length > 0 ? prod : runs).at(-1);
  return pick ? join(resultsDir, pick) : null;
}

export function readLoadRun(dir: string): { meta: LoadMeta; results: LoadResults } {
  const meta: LoadMeta = JSON.parse(readFileSync(join(dir, 'meta.json'), 'utf8'));
  const grouped = new Map<string, K6Metrics[]>();
  for (const run of meta.runs) {
    const key = `${run.suite ?? 'base'}|${run.point ?? run.scenario}|${run.contender}|${run.pass}`;
    const metrics: K6Metrics = JSON.parse(readFileSync(join(dir, run.file.split('/').pop() as string), 'utf8')).metrics;
    grouped.set(key, [...(grouped.get(key) ?? []), metrics]);
  }

  const results: LoadResults = {};
  for (const [key, list] of grouped) {
    const [suite, point, contender, pass] = key.split('|');
    const metric = (name: string, stat: string): number => median(list.map((m) => m[name]?.values[stat] ?? Number.NaN));
    const p50s = list.map((m) => m.http_req_duration.values.med);
    results[suite] ??= {};
    results[suite][point] ??= {};
    results[suite][point][contender] ??= {};
    results[suite][point][contender][pass] = {
      rps: metric('http_reqs', 'rate'),
      p50: median(p50s),
      p50Min: Math.min(...p50s),
      p50Max: Math.max(...p50s),
      p95: metric('http_req_duration', 'p(95)'),
      p99: metric('http_req_duration', 'p(99)'),
      queries: metric('api_queries', 'med'),
      appMs: metric('api_app_time', 'med'),
      memoryMb: metric('api_memory_mb', 'med'),
      payloadKb: metric('api_payload_kb', 'med'),
      errorRate: median(list.map((m) => 1 - (m.checks?.values.rate ?? 0))),
    };
  }
  return { meta, results };
}
