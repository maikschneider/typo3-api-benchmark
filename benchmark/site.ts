import { existsSync, mkdirSync, readFileSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';
import { contenders, suites } from './contenders';
import { FEATURES } from './features';
import { measureFootprints } from './footprint';
import { readLatestPerSuite, readLoadRun } from './load-results';

// Builds the self-contained overview page docs/index.html from benchmark/results/.
// Every suite comes from the newest prod run that contains it, unless a run is given.
// Usage: bun benchmark/site.ts [results/<load run>]

const root = join(import.meta.dir, '..');
const resultsDir = join(import.meta.dir, 'results');
const runArgument = process.argv[2];
const loaded = runArgument ? { ...readLoadRun(runArgument), sources: {} } : readLatestPerSuite(resultsDir);
if (!loaded) throw new Error('No load run in benchmark/results/, run `bin/bench load` first.');

const readJson = <T>(path: string): T | null => (existsSync(path) ? JSON.parse(readFileSync(path, 'utf8')) : null);

const { meta, results, sources } = loaded;
const features = readJson<{ date: string; results: Record<string, Record<string, { status: string; detail: string }>> }>(join(resultsDir, 'features.json'));

interface ProfileReport {
  summary: { wallMs: number; queries: number; peakMemory: number; includedFiles: number };
  findings: Array<{ title: string; severity: string }>;
}
const profiles = Object.fromEntries(
  contenders.map((contender) => {
    const report = readJson<ProfileReport>(join(resultsDir, 'profiles', `${contender.key}.json`));
    return [contender.key, report && { includedFiles: report.summary.includedFiles, queries: report.summary.queries, findings: report.findings.map((f) => f.title) }];
  }),
);

const ddevConfig = readFileSync(join(root, '.ddev/config.yaml'), 'utf8');
const ddevValue = (pattern: RegExp): string => ddevConfig.match(pattern)?.[1] ?? '?';

const data = {
  generated: new Date().toISOString(),
  environment: {
    date: meta.date,
    mode: meta.mode,
    vus: meta.vus,
    repetitions: meta.repetitions,
    versions: {
      ...meta.versions,
      // Isolated APIs are not in composer.lock while the run starts; their pinned versions come from contenders.ts.
      ...Object.fromEntries(
        contenders.flatMap((c) => c.isolated?.packages ?? []).map((spec) => {
          const [name, version] = spec.split(':');
          return [name, version.replace(/#(.{7}).*/, ' ($1)')];
        }),
      ),
    },
    php: ddevValue(/php_version:\s*"?([\d.]+)/),
    database: `${ddevValue(/database:\s*\n\s+type:\s*(\w+)/)} ${ddevValue(/database:\s*\n\s+type:\s*\w+\s*\n\s+version:\s*"?([\d.]+)/)}`,
  },
  contenders: contenders.map(({ key, label, package: pkg, isolated, maxPageSize }) => ({ key, label, package: pkg, isolated: isolated?.reason ?? null, maxPageSize: maxPageSize ?? null })),
  sources,
  suites: suites.map(({ key, label, axis, question, points }) => ({ key, label, axis, question, points: points.map((p) => ({ key: p.key, label: p.label, useCase: p.useCase, pageSize: p.pageSize ?? null })) })),
  load: results,
  features: features && { date: features.date, list: FEATURES, results: features.results },
  footprint: measureFootprints(),
  profiles,
};

const template = readFileSync(join(import.meta.dir, 'site', 'template.html'), 'utf8');
const json = JSON.stringify(data).replace(/</g, '\\u003c');
mkdirSync(join(root, 'docs'), { recursive: true });
writeFileSync(join(root, 'docs', 'index.html'), template.replace('"__BENCHMARK_DATA__"', json));
console.log(`docs/index.html built from ${Object.entries(sources).map(([suite, source]) => `${suite}: ${source.run}`).join(', ') || runArgument}`);
